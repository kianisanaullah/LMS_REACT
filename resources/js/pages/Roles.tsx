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

interface Role {
  ROLE_ID: number;
  ROLE_NAME: string;
}

interface User {
  id: number;
  name: string;
  email: string;
}

type FormState = {
  id: number | null;
  ROLE_NAME: string;
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: "Dashboard", href: "/dashboard" },
  { title: "Roles", href: "/roles" },
];

interface Permission {
  PERMISSION_ID: number;
  PERMISSION_NAME: string;
}
interface RawPermission {
  permission_id: string;
  permission_name: string;
}

export default function Roles() {
  const [roles, setRoles] = useState<Role[]>([]);
  const [permissions, setPermissions] = useState<Permission[]>([]);
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);

  const [selectedRole, setSelectedRole] = useState<Role | null>(null);
  const [selectedPermissions, setSelectedPermissions] = useState<number[]>([]);
  const [assigning, setAssigning] = useState(false);

  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [userRoles, setUserRoles] = useState<number[]>([]);
  const [assigningRole, setAssigningRole] = useState(false);

  const [form, setForm] = useState<FormState>({
    id: null,
    ROLE_NAME: "",
  });

  const [errors, setErrors] = useState<Partial<FormState>>({});
  const [processing, setProcessing] = useState(false);

  function mapRole(r: any): Role {
    return {
      ROLE_ID: r.ROLE_ID ?? r.role_id,
      ROLE_NAME: r.ROLE_NAME ?? r.role_name,
    };
  }

  useEffect(() => {
    Promise.all([
      axios.get("/roles/list"),
      axios.get("/permissions/list"),
      axios.get("/users/list"),
    ])
      .then(([rolesRes, permsRes, usersRes]) => {
        setRoles(rolesRes.data.map(mapRole));

        setPermissions(
          permsRes.data.map((p: any) => ({
            PERMISSION_ID: p.PERMISSION_ID ?? p.permission_id,
            PERMISSION_NAME: p.PERMISSION_NAME ?? p.permission_name,
          }))
        );

        setUsers(usersRes.data);
      })
      .finally(() => setLoading(false));
  }, []);

  const resetForm = () => {
    setForm({
      id: null,
      ROLE_NAME: "",
    });
    setErrors({});
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setProcessing(true);
    setErrors({});

    let request;
    if (form.id) {
      request = axios.put(`/roles/${form.id}`, { ROLE_NAME: form.ROLE_NAME });
    } else {
      request = axios.post("/roles", { ROLE_NAME: form.ROLE_NAME });
    }

    request
      .then((res) => {
        const updated = mapRole(res.data);
        setRoles((prev) =>
          form.id
            ? prev.map((r) => (r.ROLE_ID === form.id ? updated : r))
            : [updated, ...prev]
        );
        resetForm();
      })
      .catch((err) => {
        if (err.response?.status === 422) {
          const msg = err.response.data?.error;
          if (msg) {
            alert(msg);
            return;
          }

          const apiErrors = err.response.data?.errors || {};
          const mappedErrors: Partial<FormState> = {
            ROLE_NAME: apiErrors.ROLE_NAME || apiErrors.role_name,
          };
          setErrors(mappedErrors);
        } else {
          console.error(err);
        }
      })
      .finally(() => setProcessing(false));
  };

  const handleEdit = (role: Role) => {
    setForm({
      id: role.ROLE_ID,
      ROLE_NAME: role.ROLE_NAME,
    });
  };

  const handleDelete = (id: number) => {
    if (!confirm("Are you sure?")) return;
    axios.delete(`/roles/${id}`).then(() => {
      setRoles((prev) => prev.filter((r) => r.ROLE_ID !== id));
    });
  };

  const handleSelectRole = (role: Role) => {
    setSelectedRole(role);

    axios.get(`/roles/${role.ROLE_ID}`).then((res) => {
      const roleData = res.data;

      const normalizedPermissions: Permission[] =
        roleData.permissions?.map((p: RawPermission) => ({
          PERMISSION_ID: Number(p.permission_id),
          PERMISSION_NAME: p.permission_name,
        })) || [];

      const assignedIds = normalizedPermissions.map((p) => p.PERMISSION_ID);

      setSelectedPermissions(assignedIds);
    });
  };

  const togglePermission = (id: number) => {
    setSelectedPermissions((prev) =>
      prev.includes(id) ? prev.filter((p) => p !== id) : [...prev, id]
    );
  };

  const savePermissions = () => {
    if (!selectedRole) return;
    setAssigning(true);

    axios
      .post(`/roles/${selectedRole.ROLE_ID}/assign-permissions`, {
        permissions: selectedPermissions,
      })
      .then((res) => {
        const updatedRole = mapRole(res.data.role);
        setRoles((prev) =>
          prev.map((r) => (r.ROLE_ID === updatedRole.ROLE_ID ? updatedRole : r))
        );

        setSelectedRole(null);
        setSelectedPermissions([]);
      })
      .finally(() => setAssigning(false));
  };

  // ----------------- USER ROLE MANAGEMENT -----------------

const handleSelectUser = (user: User) => {
  setSelectedUser(user);
  axios.get(`/users/${user.id}/roles`).then((res) => {
    const assignedIds = res.data.map((r: any) => Number(r.role_id));
    setUserRoles(assignedIds);
  });
};

const toggleUserRole = (roleId: number) => {
  setUserRoles((prev) =>
    prev.includes(roleId)
      ? prev.filter((r) => r !== roleId)
      : [...prev, roleId]
  );
};

const saveUserRoles = () => {
  if (!selectedUser) return;
  setAssigningRole(true);

  axios
    .post(`/users/${selectedUser.id}/roles`, {
      roles: userRoles, // send all selected role IDs at once
    })
    .then(() => {
      setSelectedUser(null);
      setUserRoles([]);
    })
    .finally(() => setAssigningRole(false));
};

  // ----------------- RENDER -----------------

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Roles Management" />

      <div className="p-6">
        <h2 className="text-xl font-bold mb-4">Roles Management</h2>

        {/* Form */}
        <form
          onSubmit={handleSubmit}
          className="space-y-3 mb-6 border p-4 rounded"
        >
          <div>
            <Label htmlFor="role_name">Role Name</Label>
            <Input
              id="role_name"
              type="text"
              value={form.ROLE_NAME}
              onChange={(e) => setForm({ ...form, ROLE_NAME: e.target.value })}
              required
              className="focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-gray-400"
            />

            <InputError message={errors.ROLE_NAME} />
          </div>

          <Button type="submit" disabled={processing}>
            {form.id ? "Update Role" : "Add Role"}
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

        {/* Roles Table */}
        <div className="w-full overflow-x-auto mb-10">
          <table className="w-full min-w-[400px] border-collapse border rounded-lg shadow-md overflow-hidden">
            <thead>
              <tr className="bg-gray-100 dark:bg-gray-800 text-sm font-semibold text-gray-700 dark:text-gray-200">
                <th className="border px-4 py-2 text-center w-16">ID</th>
                <th className="border px-4 py-2 text-left">Role Name</th>
                <th className="border px-4 py-2 text-center w-80">Actions</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr>
                  <td colSpan={3} className="text-center p-4">
                    Loading roles...
                  </td>
                </tr>
              ) : roles.length === 0 ? (
                <tr>
                  <td colSpan={3} className="text-center p-4">
                    No roles found
                  </td>
                </tr>
              ) : (
                roles.map((role) => (
                  <tr
                    key={role.ROLE_ID}
                    className="text-sm odd:bg-white even:bg-gray-50 hover:bg-gray-100 dark:odd:bg-gray-900 dark:even:bg-gray-800 dark:hover:bg-gray-700 transition"
                  >
                    <td className="border px-3 py-2 text-center">
                      {role.ROLE_ID}
                    </td>
                    <td
                      className="border px-3 py-2 text-left truncate"
                      title={role.ROLE_NAME}
                    >
                      {role.ROLE_NAME}
                    </td>
                    <td className="border px-3 py-2 text-center">
                      <div className="flex justify-center gap-2 flex-wrap">
                        <button
                          onClick={() => handleEdit(role)}
                          className="px-3 py-1 rounded-md text-white text-sm bg-blue-600 hover:bg-blue-500 transition"
                        >
                          Edit
                        </button>
                        <button
                          onClick={() => handleDelete(role.ROLE_ID)}
                          className="px-3 py-1 rounded-md text-white text-sm bg-red-600 hover:bg-red-500 transition"
                        >
                          Delete
                        </button>
                        <button
                          onClick={() => handleSelectRole(role)}
                          className="px-3 py-1 rounded-md text-white text-sm bg-green-600 hover:bg-green-500 transition"
                        >
                          Manage Permissions
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        {/* Users Table */}
    
<div className="w-full overflow-x-auto mt-8">
  <h2 className="text-xl font-bold mb-4">Users</h2>
  <table className="w-full min-w-[400px] border-collapse border rounded-lg shadow-md overflow-hidden">
    <thead>
      <tr className="bg-gray-100 dark:bg-gray-800 text-sm font-semibold text-gray-700 dark:text-gray-200">
        <th className="border px-4 py-2 text-center w-16">ID</th>
        <th className="border px-4 py-2 text-left">Name</th>
        <th className="border px-4 py-2 text-left">Email</th>
        <th className="border px-4 py-2 text-center w-40">Actions</th>
      </tr>
    </thead>
    <tbody>
      {loading ? (
        <tr>
          <td colSpan={4} className="text-center p-4">
            Loading users...
          </td>
        </tr>
      ) : users.length === 0 ? (
        <tr>
          <td colSpan={4} className="text-center p-4">
            No users found
          </td>
        </tr>
      ) : (
        users.map((user) => (
          <tr
            key={user.id}
            className="text-sm odd:bg-white even:bg-gray-50 hover:bg-gray-100 dark:odd:bg-gray-900 dark:even:bg-gray-800 dark:hover:bg-gray-700 transition"
          >
            <td className="border px-3 py-2 text-center">{user.id}</td>
            <td
              className="border px-3 py-2 text-left truncate"
              title={user.name}
            >
              {user.name}
            </td>
            <td
              className="border px-3 py-2 text-left truncate"
              title={user.email}
            >
              {user.email}
            </td>
            <td className="border px-3 py-2 text-center">
              <button
                onClick={() => handleSelectUser(user)}
                className="px-3 py-1 rounded-md text-white text-sm bg-purple-600 hover:bg-purple-500 transition"
              >
                Manage Roles
              </button>
            </td>
          </tr>
        ))
      )}
    </tbody>
  </table>
</div>

      </div>

      {/* Permissions Modal */}
      {selectedRole && (
        <Modal isOpen={!!selectedRole} onClose={() => setSelectedRole(null)}>
          <div className="p-6 max-w-2xl">
            <h3 className="text-lg font-semibold mb-4">
              Manage Permissions for{" "}
              <span className="text-blue-600">{selectedRole.ROLE_NAME}</span>
            </h3>

            <div className="grid grid-cols-2 gap-3">
              {permissions.map((perm) => (
                <label
                  key={perm.PERMISSION_ID}
                  className="flex items-center space-x-2"
                >
                  <input
                    type="checkbox"
                    checked={selectedPermissions.includes(
                      Number(perm.PERMISSION_ID)
                    )}
                    onChange={() => togglePermission(Number(perm.PERMISSION_ID))}
                  />
                  <span>{perm.PERMISSION_NAME}</span>
                </label>
              ))}
            </div>

            <div className="mt-6 flex justify-end gap-2">
              <Button
                variant="secondary"
                onClick={() => setSelectedRole(null)}
              >
                Close
              </Button>
              <Button onClick={savePermissions} disabled={assigning}>
                Save
              </Button>
            </div>
          </div>
        </Modal>
      )}

      {/* User Roles Modal */}
      {selectedUser && (
        <Modal isOpen={!!selectedUser} onClose={() => setSelectedUser(null)}>
          <div className="p-6 max-w-2xl">
            <h3 className="text-lg font-semibold mb-4">
              Manage Roles for{" "}
              <span className="text-blue-600">{selectedUser.name}</span>
            </h3>

            <div className="grid grid-cols-2 gap-3">
              {roles.map((role) => (
                <label key={role.ROLE_ID} className="flex items-center space-x-2">
                <input
  type="checkbox"
  checked={userRoles.includes(Number(role.ROLE_ID))}
  onChange={() => toggleUserRole(Number(role.ROLE_ID))}
/>

                  <span>{role.ROLE_NAME}</span>
                </label>
              ))}
            </div>

            <div className="mt-6 flex justify-end gap-2">
              <Button variant="secondary" onClick={() => setSelectedUser(null)}>
                Close
              </Button>
              <Button onClick={saveUserRoles} disabled={assigningRole}>
                Save
              </Button>
            </div>
          </div>
        </Modal>
      )}
    </AppLayout>
  );
}
