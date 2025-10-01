import { useState } from "react";
import { Head } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import InputError from "@/components/input-error";
import axios from "axios";
import AppLayout from "@/layouts/app-layout";
import { type BreadcrumbItem } from "@/types";

// import Alert
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";

type FormState = {
  name: string;
  username: string;
  email: string;
  password: string;
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: "Dashboard", href: "/dashboard" },
  { title: "Users", href: "/users" },
];

export default function UserCreate() {
  const [form, setForm] = useState<FormState>({
    name: "",
    username: "",
    email: "",
    password: "",
  });
  const [errors, setErrors] = useState<Partial<FormState>>({});
  const [processing, setProcessing] = useState(false);

  //  State for showing alerts
  const [alert, setAlert] = useState<{
    title: string;
    description: string;
    variant?: "default" | "destructive";
  } | null>(null);

  const resetForm = () => {
    setForm({ name: "", username: "", email: "", password: "" });
    setErrors({});
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setProcessing(true);
    setErrors({});
    setAlert(null);

    axios
      .post("/users", form)
      .then(() => {
        setAlert({
          title: "Success",
          description: "User created successfully",
          variant: "default",
        });
        resetForm();
      })
      .catch((err) => {
        if (err.response?.status === 422) {
          setErrors(err.response.data.errors || {});
        } else if (err.response?.status === 403) {
          setAlert({
            title: "Forbidden",
            description: "You don’t have permission to create a user.",
            variant: "destructive",
          });
        } else {
          console.error(err);
          setAlert({
            title: "Error",
            description: "Something went wrong while creating the user.",
            variant: "destructive",
          });
        }
      })
      .finally(() => setProcessing(false));
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Create User" />

      <div className="p-6">
        <h2 className="text-xl font-bold mb-4">Create New User</h2>

        {/*  Show Alert if exists */}
        {alert && (
          <Alert variant={alert.variant} className="mb-4">
            <AlertTitle>{alert.title}</AlertTitle>
            <AlertDescription>{alert.description}</AlertDescription>
          </Alert>
        )}

        <form
          onSubmit={handleSubmit}
          className="space-y-3 mb-6 border p-4 rounded max-w-md mx-auto"
        >
          <div>
            <Label htmlFor="name">Name</Label>
            <Input
              id="name"
              type="text"
              value={form.name}
              onChange={(e) => setForm({ ...form, name: e.target.value })}
              required
            />
            <InputError message={errors.name} />
          </div>

          <div>
            <Label htmlFor="username">Username</Label>
            <Input
              id="username"
              type="text"
              value={form.username}
              onChange={(e) => setForm({ ...form, username: e.target.value })}
              required
            />
            <InputError message={errors.username} />
          </div>

          <div>
            <Label htmlFor="email">Email</Label>
            <Input
              id="email"
              type="email"
              value={form.email}
              onChange={(e) => setForm({ ...form, email: e.target.value })}
              required
            />
            <InputError message={errors.email} />
          </div>

          <div>
            <Label htmlFor="password">Password</Label>
            <Input
              id="password"
              type="password"
              value={form.password}
              onChange={(e) => setForm({ ...form, password: e.target.value })}
              required
            />
            <InputError message={errors.password} />
          </div>

          <Button type="submit" disabled={processing}>
            {processing ? "Creating..." : "Add User"}
          </Button>
        </form>
      </div>
    </AppLayout>
  );
}
