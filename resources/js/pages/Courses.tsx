import { useEffect, useState } from "react";
import { Head } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import InputError from "@/components/input-error";
import axios from "axios";
import AppLayout from "@/layouts/app-layout";   
import { type BreadcrumbItem } from "@/types";

interface Course {
  ID: number;
  COURSE_NAME: string;
  SHORT_NAME: string;
  DESCRIPTION?: string;
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Dashboard",
    href: "/dashboard",
  },
  {
    title: "Courses",
    href: "/courses",
  },
];

export default function Courses() {
  const [courses, setCourses] = useState<Course[]>([]);
  const [form, setForm] = useState({
    id: null as number | null,
    COURSE_NAME: "",
    SHORT_NAME: "",
    DESCRIPTION: "",
  });
  const [errors, setErrors] = useState<Partial<typeof form>>({});
  const [processing, setProcessing] = useState(false);

  function mapCourse(c: any): Course {
    return {
      ID: c.id,
      COURSE_NAME: c.course_name,
      SHORT_NAME: c.short_name,
      DESCRIPTION: c.description,
    };
  }

  // Load courses
  useEffect(() => {
    axios.get("/courses/list").then((res) => {
      setCourses(res.data.map(mapCourse));
    });
  }, []);

  const resetForm = () => {
    setForm({ id: null, COURSE_NAME: "", SHORT_NAME: "", DESCRIPTION: "" });
    setErrors({});
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setProcessing(true);
    setErrors({});

    if (form.id) {
      // Update
      axios
        .put(`/courses/${form.id}`, {
          COURSE_NAME: form.COURSE_NAME,
          SHORT_NAME: form.SHORT_NAME,
          DESCRIPTION: form.DESCRIPTION,
        })
        .then((res) => {
          const updated = mapCourse(res.data);
          setCourses((prev) =>
            prev.map((c) => (c.ID === form.id ? updated : c))
          );
          resetForm();
        })
        .catch((err) => {
          setErrors(err.response?.data?.errors || {});
        })
        .finally(() => setProcessing(false));
    } else {
      // Create
      axios
        .post("/courses", {
          COURSE_NAME: form.COURSE_NAME,
          SHORT_NAME: form.SHORT_NAME,
          DESCRIPTION: form.DESCRIPTION,
        })
        .then((res) => {
          setCourses((prev) => [...prev, mapCourse(res.data)]);
          resetForm();
        })
        .catch((err) => {
          setErrors(err.response?.data?.errors || {});
        })
        .finally(() => setProcessing(false));
    }
  };

  const handleEdit = (course: Course) => {
    setForm({
      id: course.ID,
      COURSE_NAME: course.COURSE_NAME,
      SHORT_NAME: course.SHORT_NAME,
      DESCRIPTION: course.DESCRIPTION ?? "",
    });
  };

  const handleDelete = (id: number) => {
    if (!confirm("Are you sure?")) return;
    axios.delete(`/courses/${id}`).then(() => {
      setCourses((prev) => prev.filter((c) => c.ID !== id));
    });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Courses Management" />

      <div className="p-6">
        <h2 className="text-xl font-bold mb-4">Courses Management</h2>

        {/* Form */}
        <form
          onSubmit={handleSubmit}
          className="space-y-3 mb-6 border p-4 rounded"
        >
          <div>
            <Label htmlFor="course_name">Course Name</Label>
            <Input
              id="course_name"
              type="text"
              value={form.COURSE_NAME}
              onChange={(e) =>
                setForm({ ...form, COURSE_NAME: e.target.value })
              }
              required
            />
            <InputError message={errors.COURSE_NAME} />
          </div>

          <div>
            <Label htmlFor="short_name">Short Name</Label>
            <Input
              id="short_name"
              type="text"
              value={form.SHORT_NAME}
              onChange={(e) =>
                setForm({ ...form, SHORT_NAME: e.target.value })
              }
            />
            <InputError message={errors.SHORT_NAME} />
          </div>

          <div>
            <Label htmlFor="description">Description</Label>
            <Input
              id="description"
              type="text"
              value={form.DESCRIPTION}
              onChange={(e) =>
                setForm({ ...form, DESCRIPTION: e.target.value })
              }
            />
            <InputError message={errors.DESCRIPTION} />
          </div>

          <Button type="submit" disabled={processing}>
            {form.id ? "Update Course" : "Add Course"}
          </Button>

          {form.id && (
            <Button
              type="button"
              variant="secondary"
              className="ml-2"
              onClick={resetForm}
            >
              Cancel
            </Button>
          )}
        </form>

        {/* Table */}
        <table className="w-full border-collapse border">
          <thead>
            <tr className="bg-gray-200">
              <th className="border p-2">ID</th>
              <th className="border p-2">Course Name</th>
              <th className="border p-2">Short Name</th>
              <th className="border p-2">Actions</th>
            </tr>
          </thead>
          <tbody>
            {courses.map((course) => (
              <tr key={course.ID}>
                <td className="border p-2">{course.ID}</td>
                <td className="border p-2">{course.COURSE_NAME}</td>
                <td className="border p-2">{course.SHORT_NAME}</td>
                <td className="border p-2 space-x-2">
                  <Button
                    onClick={() => handleEdit(course)}
                    variant="secondary"
                    size="sm"
                  >
                    Edit
                  </Button>
                  <Button
                    onClick={() => handleDelete(course.ID)}
                    variant="destructive"
                    size="sm"
                  >
                    Delete
                  </Button>
                </td>
              </tr>
            ))}
            {courses.length === 0 && (
              <tr>
                <td colSpan={4} className="text-center p-4">
                  No courses found
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </AppLayout>
  );
}
