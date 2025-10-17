import { useEffect, useState } from "react";
import axios from "axios";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Users, UserCog, UserCheck, BookOpen, ArrowRight } from "lucide-react";
import AppLayout from "@/layouts/app-layout";
import { type BreadcrumbItem } from "@/types";
import { Head, Link } from "@inertiajs/react";
import { PlaceholderPattern } from "@/components/ui/placeholder-pattern";

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: "Dashboard",
    href: "/dashboard",
  },
];

export default function Dashboard() {
  const [roleCounts, setRoleCounts] = useState<
    { role_name: string; total_users: number }[]
  >([]);
  const [loading, setLoading] = useState(true);
  const [isAdmin, setIsAdmin] = useState(true);
  const [username, setUsername] = useState<string>("");
  const [courseCount, setCourseCount] = useState<number | null>(null);

  useEffect(() => {
    const fetchUser = async () => {
      try {
        const res = await axios.get("/test-auth");
        setUsername(res.data?.name || "User");
      } catch {
        setUsername("User");
      }
    };

    const fetchCounts = async () => {
      try {
        const response = await axios.get("/users/roles/count");
        setRoleCounts(response.data);
        setIsAdmin(true);
      } catch (error: any) {
        if (error.response && error.response.status === 403) {
          setIsAdmin(false);
        } else {
          console.error("Error fetching role counts:", error);
        }
      } finally {
        setLoading(false);
      }
    };

    const fetchCourses = async () => {
      try {
        const res = await axios.get("/courses/count");
        setCourseCount(res.data?.total_courses ?? 0);
      } catch (err) {
        console.error("Error fetching course count:", err);
      }
    };

    fetchUser();
    fetchCounts();
    fetchCourses();
  }, []);

  const getIcon = (roleName: string) => {
    switch (roleName.toLowerCase()) {
      case "admin":
        return <UserCog className="text-blue-600 size-10" />;
      case "instructor":
        return <UserCheck className="text-green-600 size-10" />;
      case "participant":
        return <Users className="text-orange-500 size-10" />;
      default:
        return <Users className="text-gray-500 size-10" />;
    }
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Dashboard" />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
        {/* ==== Top Grid ==== */}
        <div className="grid auto-rows-min gap-4 md:grid-cols-3">
          {/* === Card 1: Role Overview / Greeting === */}
          <Card className="relative aspect-auto overflow-hidden rounded-2xl border border-gray-200 shadow-md bg-gradient-to-br from-orange-50 to-white dark:from-gray-900 dark:to-gray-800">
            <CardHeader>
              <CardTitle className="text-xl font-bold text-gray-800 dark:text-gray-100 flex justify-between items-center">
                {isAdmin ? (
                  <span>Role Overview</span>
                ) : (
                  <span>Hi, {username}</span>
                )}
              </CardTitle>
            </CardHeader>
            <CardContent className="flex justify-around items-center py-4">
              {loading ? (
                <div className="text-center text-gray-500">Loading...</div>
              ) : isAdmin ? (
                roleCounts.map((role) => (
                  <div
                    key={role.role_name}
                    className="flex flex-col items-center justify-center text-center"
                  >
                    {getIcon(role.role_name)}
                    <p className="mt-2 text-lg font-semibold text-gray-700 dark:text-gray-100">
                      {role.role_name}
                    </p>
                    <p className="text-3xl font-bold text-gray-900 dark:text-white">
                      {role.total_users}
                    </p>
                  </div>
                ))
              ) : (
                <div className="text-gray-500 text-center italic">
                  Welcome to your dashboard 👋
                </div>
              )}
            </CardContent>
          </Card>

          {/* === Card 2: Courses Count === */}
          <Card className="relative aspect-auto overflow-hidden rounded-2xl border border-gray-200 shadow-md bg-gradient-to-br from-blue-50 to-white dark:from-gray-900 dark:to-gray-800">
            <CardHeader>
              <CardTitle className="text-xl font-bold text-gray-800 dark:text-gray-100 flex justify-between items-center">
                Courses Overview
              </CardTitle>
            </CardHeader>
            <CardContent className="flex flex-col items-center justify-center py-6">
              <BookOpen className="text-blue-600 size-12 mb-2" />
              {courseCount === null ? (
                <p className="text-gray-500">Loading...</p>
              ) : (
                <>
                  <p className="text-3xl font-bold text-gray-900 dark:text-white">
                    {courseCount}
                  </p>
                  <p className="text-lg text-gray-700 dark:text-gray-200">
                    Total Courses
                  </p>
                  {/* === Link to Courses === */}
                  <Link
                    href="/courses"
                    className="mt-3 inline-flex items-center gap-1 text-blue-600 hover:underline text-sm font-medium"
                  >
                    Go to Courses <ArrowRight size={16} />
                  </Link>
                </>
              )}
            </CardContent>
          </Card>

          {/* === Placeholder Card === */}
          <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
          </div>
        </div>

        {/* ==== Main Content Area ==== */}
        <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
          <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
        </div>
      </div>
    </AppLayout>
  );
}
