import { useEffect, useState, useRef } from "react";
import { Head } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import InputError from "@/components/input-error";
import axios from "axios";
import AppLayout from "@/layouts/app-layout";   
import { type BreadcrumbItem } from "@/types";
import Modal from "@/components/ui/modal";


interface Course {
  ID: number;
  COURSE_NAME: string;
}

interface Subcourse {
  ID: number;
  COURSE_ID: number;
  SUBCOURSE_NAME: string;
  DESCRIPTION?: string;
  ATTACHMENTS?: string | null;
  ATTACHMENT_URL?: string | null;
  COURSE_NAME?: string; // added for UI rendering
}

type FormState = {
  id: number | null;
  COURSE_ID: number | null;
  SUBCOURSE_NAME: string;
  DESCRIPTION: string;
  ATTACHMENTS: File | string | null;
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: "Dashboard", href: "/dashboard" },
  { title: "Subcourses", href: "/subcourses" },
];

export default function Subcourses() {
  const [subcourses, setSubcourses] = useState<Subcourse[]>([]);
  const [courses, setCourses] = useState<Course[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedSubcourse, setSelectedSubcourse] = useState<any | null>(null);
   const fileInputRef = useRef<HTMLInputElement | null>(null);

  const [form, setForm] = useState<FormState>({
    id: null,
    COURSE_ID: null,
    SUBCOURSE_NAME: "",
    DESCRIPTION: "",
    ATTACHMENTS: null,
  });

  const [errors, setErrors] = useState<Partial<FormState>>({});
  const [processing, setProcessing] = useState(false);

  function mapSubcourse(s: any): Subcourse {
    const file = s.ATTACHMENTS ?? s.attachments ?? null;
    return {
      ID: s.ID ?? s.id,
      COURSE_ID: s.COURSE_ID ?? s.course_id,
      SUBCOURSE_NAME: s.SUBCOURSE_NAME ?? s.subcourse_name,
      DESCRIPTION: s.DESCRIPTION ?? s.description,
      ATTACHMENTS: file,
      ATTACHMENT_URL: s.attachment_url ?? (file ? `/storage/${file}` : null),
      COURSE_NAME: s.COURSE_NAME ?? s.course_name, 
    };
  }

  useEffect(() => {
    Promise.all([
      axios.get("/subcourses/list"),
      axios.get("/courses/list"),
    ])
    .then(([subRes, courseRes]) => {
  const courseList: Course[] = courseRes.data.map((c: any) => ({
    ID: c.ID ?? c.id,
    COURSE_NAME: c.COURSE_NAME ?? c.course_name,
  }));
  setCourses(courseList);

  setSubcourses(
    subRes.data.map((s: any) => {
      const mapped = mapSubcourse(s);
      const course = courseList.find((c: Course) => c.ID === mapped.COURSE_ID);
      return { ...mapped, COURSE_NAME: course?.COURSE_NAME };
    })
  );
})

      .finally(() => setLoading(false));
  }, []);

  const resetForm = () => {
    setForm({
      id: null,
      COURSE_ID: null,
      SUBCOURSE_NAME: "",
      DESCRIPTION: "",
      ATTACHMENTS: null,
    });
    setErrors({});

    // clear file input field manually
  if (fileInputRef.current) {
    fileInputRef.current.value = "";
  }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setProcessing(true);
    setErrors({});

    const formData = new FormData();
    if (form.COURSE_ID) formData.append("COURSE_ID", String(form.COURSE_ID));
    formData.append("SUBCOURSE_NAME", form.SUBCOURSE_NAME);
    formData.append("DESCRIPTION", form.DESCRIPTION);

    if (form.ATTACHMENTS) {
      formData.append("ATTACHMENTS", form.ATTACHMENTS);
    }

    let request;
    if (form.id) {
      formData.append("_method", "PUT");
      request = axios.post(`/subcourses/${Number(form.id)}`, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
    } else {
      request = axios.post("/subcourses", formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
    }

    request
      .then((res) => {
        const updated = mapSubcourse(res.data);

     
        const course = courses.find((c) => c.ID === updated.COURSE_ID);
        if (course) updated.COURSE_NAME = course.COURSE_NAME;

        setSubcourses((prev) =>
          form.id
            ? prev.map((s) => (s.ID === form.id ? updated : s))
            : [updated, ...prev]
        );
        resetForm();
      })
      .catch((err) => setErrors(err.response?.data?.errors || {}))
      .finally(() => setProcessing(false));
  };

  const handleEdit = (sub: Subcourse) => {
    setForm({
      id: sub.ID,
      COURSE_ID: sub.COURSE_ID,
      SUBCOURSE_NAME: sub.SUBCOURSE_NAME,
      DESCRIPTION: sub.DESCRIPTION ?? "",
      ATTACHMENTS: null,
    });
  };

  const handleDelete = (id: number) => {
    if (!confirm("Are you sure?")) return;
    axios.delete(`/subcourses/${id}`).then(() => {
      setSubcourses((prev) => prev.filter((s) => s.ID !== id));
    });
  };

 return (
  <AppLayout breadcrumbs={breadcrumbs}>
    <Head title="Subcourses Management" />

    <div className="p-6">
      <h2 className="text-xl font-bold mb-4">Subcourses Management</h2>

      {/* Form */}
      <form
        onSubmit={handleSubmit}
        className="space-y-3 mb-6 border p-4 rounded"
        encType="multipart/form-data"
      >
        {/* Dropdown for Course */}
        <div>
          <Label htmlFor="course_id">Select Course</Label>
          <select
            id="course_id"
            value={form.COURSE_ID ?? ""}
            onChange={(e) =>
              setForm({ ...form, COURSE_ID: Number(e.target.value) })
            }
            required
            className="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-gray-400"
          >
            <option value="">-- Select Course --</option>
            {courses.map((course: Course) => (
              <option key={course.ID} value={course.ID}>
                {course.COURSE_NAME}
              </option>
            ))}
          </select>
          {/* <InputError message={errors.COURSE_ID} /> */}
        </div>

        {/* Subcourse Name */}
        <div>
          <Label htmlFor="subcourse_name">Subcourse Name</Label>
          <Input
            id="subcourse_name"
            type="text"
            value={form.SUBCOURSE_NAME}
            onChange={(e) =>
              setForm({ ...form, SUBCOURSE_NAME: e.target.value })
            }
            required
          />
          <InputError message={errors.SUBCOURSE_NAME} />
        </div>

        {/* Description */}
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

        {/* Attachments */}
        <div>
          <Label htmlFor="attachments">Attachments</Label>
          <input
            id="attachments"
            type="file"
            ref={fileInputRef}
            onChange={(e) =>
              setForm({
                ...form,
                ATTACHMENTS: e.target.files?.[0] || null,
              })
            }
            className="w-full border rounded px-3 py-2 text-sm file:mr-4 file:py-2 file:px-4 
                       file:rounded file:border-0 file:text-sm file:font-semibold 
                       file:bg-gray-100 file:text-gray-700 
                       hover:file:bg-gray-200 focus:outline-none focus:ring-2 
                       focus:ring-gray-400 focus:border-gray-400"
          />
        </div>

        <Button type="submit" disabled={processing}>
          {form.id ? "Update Subcourse" : "Add Subcourse"}
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
    <tr className="bg-gray-100 dark:bg-gray-800 text-sm font-semibold text-gray-700 dark:text-gray-200">
      <th className="border dark:border-gray-700 px-4 py-2 text-center w-16">ID</th>
      <th className="border dark:border-gray-700 px-4 py-2 text-left">Subcourse Name</th>
      <th className="border dark:border-gray-700 px-4 py-2 text-left">Course</th>
      <th className="border dark:border-gray-700 px-4 py-2 text-left">Attachments</th>
      <th className="border dark:border-gray-700 px-4 py-2 text-center w-60">Actions</th>
    </tr>
  </thead>
  <tbody>
    {loading ? (
      <tr>
        <td colSpan={5} className="text-center p-4">
          Loading subcourses...
        </td>
      </tr>
    ) : subcourses.length === 0 ? (
      <tr>
        <td colSpan={5} className="text-center p-4">
          No subcourses found
        </td>
      </tr>
    ) : (
      subcourses.map((sub: Subcourse) => (
        <tr
          key={sub.ID}
          className="text-sm odd:bg-white even:bg-gray-50 hover:bg-gray-100 
                     dark:odd:bg-gray-900 dark:even:bg-gray-800 dark:hover:bg-gray-700 
                     transition"
        >
          <td className="border dark:border-gray-700 px-3 py-2 text-center">{sub.ID}</td>
          <td className="border dark:border-gray-700 px-3 py-2">{sub.SUBCOURSE_NAME}</td>
          <td className="border dark:border-gray-700 px-3 py-2">
            {courses.find((c: Course) => c.ID === sub.COURSE_ID)?.COURSE_NAME || "—"}
          </td>
          <td className="border dark:border-gray-700 px-3 py-2 text-center">
            {sub.ATTACHMENT_URL ? (
              <a
                href={sub.ATTACHMENT_URL}
                target="_blank"
                rel="noopener noreferrer"
                className="px-3 py-1 rounded-md text-white text-sm bg-green-600 hover:bg-green-500 transition"
              >
                Open File
              </a>
            ) : (
              <span className="text-gray-400 dark:text-gray-500">No File</span>
            )}
          </td>
          <td className="border dark:border-gray-700 px-3 py-2 text-center">
            <div className="flex justify-center gap-2">
              <button
                onClick={() => handleEdit(sub)}
                className="px-3 py-1 rounded-md text-white text-sm bg-blue-600 hover:bg-blue-500 transition"
              >
                Edit
              </button>
              <button
                onClick={() => handleDelete(sub.ID)}
                className="px-3 py-1 rounded-md text-white text-sm bg-red-600 hover:bg-red-500 transition"
              >
                Delete
              </button>
              <button
                onClick={() => setSelectedSubcourse(sub)}
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

    </div>
    <Modal
  isOpen={!!selectedSubcourse}
  onClose={() => setSelectedSubcourse(null)}
  title={selectedSubcourse?.SUBCOURSE_NAME}
  widthClass="max-w-4xl"
>
  {selectedSubcourse && (
    <div className="space-y-4">
      <p>
        <strong>ID:</strong> {selectedSubcourse.ID}
      </p>
      <p>
        <strong>Course:</strong> {selectedSubcourse.COURSE_NAME || "—"}
      </p>
      <p>
        <strong>Description:</strong>{" "}
        {selectedSubcourse.DESCRIPTION || "No description"}
      </p>
      <p>
        <strong>Attachment:</strong>{" "}
        {selectedSubcourse.ATTACHMENT_URL ? (
          <a
            href={selectedSubcourse.ATTACHMENT_URL}
            target="_blank"
            rel="noopener noreferrer"
            className="text-blue-600 hover:underline"
          >
            Open File
          </a>
        ) : (
          "No File"
        )}
      </p>
    </div>
  )}
</Modal>

  </AppLayout>
);
}
