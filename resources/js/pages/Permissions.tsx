import { useEffect, useState } from "react";
import { Head } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import InputError from "@/components/input-error";
import axios from "axios";
import AppLayout from "@/layouts/app-layout";
import { type BreadcrumbItem } from "@/types";
import Modal from "@/components/ui/modal";

interface Permission {
  PERMISSION_ID: number;
  PERMISSION_NAME: string;
}

type FormState = {
  id: number | null;
  PERMISSION_NAME: string;
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: "Dashboard", href: "/dashboard" },
  { title: "Permissions", href: "/permissions" },
];

export default function Permissions() {
  const [permissions, setPermissions] = useState<Permission[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedPermission, setSelectedPermission] = useState<Permission | null>(null);

  const [form, setForm] = useState<FormState>({
    id: null,
    PERMISSION_NAME: "",
  });

  const [errors, setErrors] = useState<Partial<FormState>>({});
  const [processing, setProcessing] = useState(false);

  function mapPermission(p: any): Permission {
    return {
      PERMISSION_ID: p.PERMISSION_ID ?? p.permission_id,
      PERMISSION_NAME: p.PERMISSION_NAME ?? p.permission_name,
    };
  }

  useEffect(() => {
    axios
      .get("/permissions/list")
      .then((res) => setPermissions(res.data.map(mapPermission)))
      .finally(() => setLoading(false));
  }, []);

  const resetForm = () => {
    setForm({
      id: null,
      PERMISSION_NAME: "",
    });
    setErrors({});
  };

 const handleSubmit = (e: React.FormEvent) => {
  e.preventDefault();
  setProcessing(true);
  setErrors({});

  let request;
  if (form.id) {
    // PUT for update
    request = axios.put(`/permissions/${form.id}`, {
      PERMISSION_NAME: form.PERMISSION_NAME,
    });
  } else {
    // POST for create
    request = axios.post("/permissions", {
      PERMISSION_NAME: form.PERMISSION_NAME,
    });
  }

  request
    .then((res) => {
      const updated = mapPermission(res.data);
      setPermissions((prev) =>
        form.id
          ? prev.map((p) => (p.PERMISSION_ID === form.id ? updated : p))
          : [updated, ...prev]
      );
      resetForm();
    })
    .catch((err) => {
      //  Backend validation errors (Laravel)
      if (err.response?.status === 422) {
        // Show backend "error" message if present
        const msg = err.response.data?.error;
        if (msg) {
          alert(msg);
          return;
        }
        // Fallback to field errors (validation rules)
        setErrors(err.response.data?.errors || {});
      } else {
        console.error(err);
      }
    })
    .finally(() => setProcessing(false));
};

  const handleEdit = (permission: Permission) => {
    setForm({
      id: permission.PERMISSION_ID,
      PERMISSION_NAME: permission.PERMISSION_NAME,
    });
  };

  const handleDelete = (id: number) => {
    if (!confirm("Are you sure?")) return;
    axios.delete(`/permissions/${id}`).then(() => {
      setPermissions((prev) => prev.filter((p) => p.PERMISSION_ID !== id));
    });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Permissions Management" />

      <div className="p-6">
        <h2 className="text-xl font-bold mb-4">Permissions Management</h2>

        {/* Form */}
        <form
          onSubmit={handleSubmit}
          className="space-y-3 mb-6 border p-4 rounded"
        >
          <div>
            <Label htmlFor="permission_name">Permission Name</Label>
            <Input
              id="permission_name"
              type="text"
              value={form.PERMISSION_NAME}
              onChange={(e) =>
                setForm({ ...form, PERMISSION_NAME: e.target.value })
              }
              required
              className="focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-gray-400"
            />
            <InputError message={errors.PERMISSION_NAME} />
          </div>

          <Button type="submit" disabled={processing}>
            {form.id ? "Update Permission" : "Add Permission"}
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
        <div className="w-full overflow-x-auto">
          <table className="w-full min-w-[400px] border-collapse border rounded-lg shadow-md overflow-hidden">
            <thead>
              <tr className="bg-gray-100 dark:bg-gray-800 text-sm font-semibold text-gray-700 dark:text-gray-200">
                <th className="border px-4 py-2 text-center w-16">ID</th>
                <th className="border px-4 py-2 text-left">Permission Name</th>
                <th className="border px-4 py-2 text-center w-60">Actions</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr>
                  <td colSpan={3} className="text-center p-4">
                    Loading permissions...
                  </td>
                </tr>
              ) : permissions.length === 0 ? (
                <tr>
                  <td colSpan={3} className="text-center p-4">
                    No permissions found
                  </td>
                </tr>
              ) : (
                permissions.map((permission) => (
                  <tr
                    key={permission.PERMISSION_ID}
                    className="text-sm odd:bg-white even:bg-gray-50 hover:bg-gray-100 dark:odd:bg-gray-900 dark:even:bg-gray-800 dark:hover:bg-gray-700 transition"
                  >
                    <td className="border px-3 py-2 text-center">
                      {permission.PERMISSION_ID}
                    </td>
                    <td
                      className="border px-3 py-2 text-left truncate"
                      title={permission.PERMISSION_NAME}
                    >
                      {permission.PERMISSION_NAME}
                    </td>
                    <td className="border px-3 py-2 text-center">
                      <div className="flex justify-center gap-2 flex-wrap">
                        <button
                          onClick={() => handleEdit(permission)}
                          className="px-3 py-1 rounded-md text-white text-sm bg-blue-600 hover:bg-blue-500 transition"
                        >
                          Edit
                        </button>
                        <button
                          onClick={() => handleDelete(permission.PERMISSION_ID)}
                          className="px-3 py-1 rounded-md text-white text-sm bg-red-600 hover:bg-red-500 transition"
                        >
                          Delete
                        </button>
                      
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </AppLayout>
  );
}
