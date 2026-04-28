import { Link, useLocation } from "wouter";
import { Activity, Calendar, Heart, Pill, Stethoscope, Users } from "lucide-react";
import { cn } from "@/lib/utils";

const navigation = [
  { name: "Главная", href: "/", icon: Activity },
  { name: "Врачи", href: "/doctors", icon: Stethoscope },
  { name: "Пациенты", href: "/patients", icon: Users },
  { name: "Болезни", href: "/diseases", icon: Pill },
  { name: "Журнал приёмов", href: "/visits", icon: Calendar },
];

export function Layout({ children }: { children: React.ReactNode }) {
  const [location] = useLocation();

  return (
    <div className="flex h-screen bg-background">
      <div className="w-64 border-r bg-sidebar flex flex-col">
        <div className="h-16 flex items-center px-6 border-b border-sidebar-border text-sidebar-primary">
          <Heart className="w-6 h-6 mr-2" />
          <span className="font-bold text-lg">Поликлиника</span>
        </div>
        <nav className="flex-1 py-4">
          <ul className="space-y-1 px-3">
            {navigation.map((item) => {
              const isActive = location === item.href || (item.href !== "/" && location.startsWith(item.href));
              return (
                <li key={item.name}>
                  <Link href={item.href}>
                    <div
                      className={cn(
                        "flex items-center px-3 py-2 text-sm font-medium rounded-md cursor-pointer transition-colors",
                        isActive
                          ? "bg-sidebar-primary text-sidebar-primary-foreground"
                          : "text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                      )}
                    >
                      <item.icon className={cn("mr-3 h-5 w-5", isActive ? "text-sidebar-primary-foreground" : "text-sidebar-foreground/70")} />
                      {item.name}
                    </div>
                  </Link>
                </li>
              );
            })}
          </ul>
        </nav>
      </div>
      <main className="flex-1 overflow-y-auto">
        <div className="p-8">
          {children}
        </div>
      </main>
    </div>
  );
}
