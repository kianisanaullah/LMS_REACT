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
  const [loading, setLoading] = useState(true);
   // modal
  const [selectedCourse, setSelectedCourse] = useState<Course | null>(null);

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
    ID: c.ID ?? c.id,
    COURSE_NAME: c.COURSE_NAME ?? c.course_name,
    SHORT_NAME: c.SHORT_NAME ?? c.short_name,
    DESCRIPTION: c.DESCRIPTION ?? c.description,
  };
}


  useEffect(() => {
  axios.get("/courses/list").then((res) => {
    setCourses(res.data.map(mapCourse));
  })
  .finally(() => setLoading(false)); // mark loading complete
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
    setCourses((prev) => [mapCourse(res.data), ...prev]); // prepend instead of append
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
      className="focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-gray-400"
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
      className="focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-gray-400"
    />
    <InputError message={errors.SHORT_NAME} />
  </div>

  <div>
    <Label htmlFor="description">Description</Label>
    <textarea
      id="description"
      value={form.DESCRIPTION}
      onChange={(e) =>
        setForm({ ...form, DESCRIPTION: e.target.value })
      }
      rows={4}
      className="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-gray-400"
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
        <table className="w-full border-collapse border rounded-lg shadow-md overflow-hidden">
          <thead>
            <tr className="bg-gray-100 text-sm font-semibold text-gray-700">
              <th className="border px-4 py-2 text-center w-16">ID</th>
              <th className="border px-4 py-2 text-left w-64">Course Name</th>
              <th className="border px-4 py-2 text-left w-40">Short Name</th>
              <th className="border px-4 py-2 text-center w-60">Actions</th>
            </tr>
          </thead>

          <tbody>
            {loading ? (
              <tr>
                <td colSpan={4} className="text-center p-4">
                  Loading courses...
                </td>
              </tr>
            ) : courses.length === 0 ? (
              <tr>
                <td colSpan={4} className="text-center p-4">
                  No courses found
                </td>
              </tr>
            ) : (
              courses.map((course) => (
                <tr
                  key={course.ID}
                  className="text-sm odd:bg-white even:bg-gray-50 hover:bg-gray-100 transition"
                >
                  <td className="border px-3 py-2 text-center">{course.ID}</td>
                  <td
                    className="border px-3 py-2 text-left w-64 max-w-xs truncate"
                    title={course.COURSE_NAME}
                  >
                    {course.COURSE_NAME}
                  </td>
                  <td className="border px-3 py-2 text-left">{course.SHORT_NAME}</td>
                  <td className="border px-3 py-2 text-center">
                    <div className="flex justify-center gap-2">
                      {/* Edit Button */}
                      <button
                        onClick={() => handleEdit(course)}
                        className="px-3 py-1 rounded-md text-white text-sm bg-blue-600 hover:bg-blue-500 transition"
                      >
                        Edit
                      </button>

                      {/* Delete Button */}
                      <button
                        onClick={() => handleDelete(course.ID)}
                        className="px-3 py-1 rounded-md text-white text-sm bg-red-600 hover:bg-red-500 transition"
                      >
                        Delete
                      </button>

                      {/* View Button */}
                      <button
                        onClick={() => setSelectedCourse(course)}
                        className="px-3 py-1 rounded-md text-white text-sm bg-gray-700 hover:bg-gray-600 transition"
                      >
                        View
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>

       {selectedCourse && (
  <div
    className="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-30 backdrop-blur-sm z-50 p-4"
    onClick={() => setSelectedCourse(null)} // close when clicking outside
  >
    <div
      className="bg-white rounded-lg shadow-lg w-full max-w-2xl max-h-[85vh] overflow-y-auto p-6 relative"
      onClick={(e) => e.stopPropagation()} // prevent close when clicking inside
    >
      {/* Close Button */}
      <button
        className="absolute top-3 right-3 text-gray-500 hover:text-gray-800"
        onClick={() => setSelectedCourse(null)}
      >
        ✕
      </button>

      <h3 className="text-xl font-bold mb-4 text-center">Course Details</h3>

      <div className="space-y-3">
        <div>
          <p className="text-sm font-semibold text-gray-600">Course Name</p>
          <p className="text-gray-800">{selectedCourse.COURSE_NAME}</p>
        </div>
        <div>
          <p className="text-sm font-semibold text-gray-600">Short Name</p>
          <p className="text-gray-800">{selectedCourse.SHORT_NAME}</p>
        </div>
        <div>
          <p className="text-sm font-semibold text-gray-600">Description</p>
          <p className="text-gray-800 whitespace-pre-line">
            {selectedCourse.DESCRIPTION || "No description available"}
          </p>
        </div>
      </div>
    </div>
  </div>
)}

      </div>
    </AppLayout>
  );
}
