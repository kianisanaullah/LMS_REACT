import { usePage, Link } from "@inertiajs/react";
import { NavFooter } from "@/components/nav-footer";
import { NavMain } from "@/components/nav-main";
import { NavUser } from "@/components/nav-user";
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/components/ui/sidebar";
import { type NavItem } from "@/types";
import { BookOpen, Folder, LayoutGrid, ShieldCheck, UserPlus } from "lucide-react";
import AppLogo from "./app-logo";

export function AppSidebar() {
  const { props } = usePage<{ auth?: { user?: any; isAdmin?: boolean; roles?: string[] } }>();
  console.log("Inertia auth props:", props.auth);

  const isAdmin = props.auth?.isAdmin ?? false;

  const mainNavItems: NavItem[] = [
    { title: "Dashboard", href: "/dashboard", icon: LayoutGrid },
    { title: "Courses", href: "/courses", icon: BookOpen },
    { title: "Subcourses", href: "/subcourses", icon: BookOpen },
    ...(isAdmin
      ? [
          { title: "Roles", href: "/roles", icon: ShieldCheck },
          { title: "Create User", href: "/users/create", icon: UserPlus },
          { title: "Permissions", href: "/permissions", icon: ShieldCheck },
        ]
      : []),
    
  ];

  const footerNavItems: NavItem[] = [
    { title: "Repository", href: "https://github.com/laravel/react-starter-kit", icon: Folder },
    { title: "Documentation", href: "https://laravel.com/docs/starter-kits#react", icon: BookOpen },
  ];

  return (
    <Sidebar collapsible="icon" variant="inset">
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" asChild>
              <Link href="/dashboard" prefetch>
                <AppLogo />
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>

      <SidebarContent>
        <NavMain items={mainNavItems} />
      </SidebarContent>

      <SidebarFooter>
        <NavFooter items={footerNavItems} className="mt-auto" />
        <NavUser />
      </SidebarFooter>
    </Sidebar>
  );
}
