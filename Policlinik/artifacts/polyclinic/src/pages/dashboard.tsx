import { useGetStatsOverview, useGetVisitsByDay, useGetTopDiseases, useGetDoctorLoad, useGetRecentActivity } from "@workspace/api-client-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Users, Stethoscope, Pill, Calendar, Activity as ActivityIcon } from "lucide-react";
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, CartesianGrid, Cell } from "recharts";
import { motion } from "framer-motion";
import { format } from "date-fns";
import { ru } from "date-fns/locale";
import heroImg from "@/assets/images/dashboard-hero.jpg";

export default function Dashboard() {
  const { data: stats } = useGetStatsOverview();
  const { data: visitsByDay } = useGetVisitsByDay({ days: 7 });
  const { data: topDiseases } = useGetTopDiseases();
  const { data: doctorLoad } = useGetDoctorLoad();
  const { data: recentActivity } = useGetRecentActivity();

  return (
    <div className="space-y-8 pb-10">
      <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="relative overflow-hidden rounded-2xl bg-primary/10 text-primary-foreground h-48 flex items-center"
      >
        <img src={heroImg} alt="Clinic Hero" className="absolute inset-0 w-full h-full object-cover opacity-20 mix-blend-multiply" />
        <div className="relative z-10 px-8">
          <h1 className="text-3xl font-bold text-primary">Добро пожаловать в систему управления</h1>
          <p className="mt-2 text-primary/80">Обзор показателей поликлиники на сегодня.</p>
        </div>
      </motion.div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <StatCard title="Врачи" value={stats?.totalDoctors} icon={Stethoscope} delay={0.1} />
        <StatCard title="Пациенты" value={stats?.totalPatients} icon={Users} delay={0.2} />
        <StatCard title="Приёмы (Сегодня)" value={stats?.visitsToday} icon={Calendar} delay={0.3} />
        <StatCard title="Заболевания" value={stats?.totalDiseases} icon={Pill} delay={0.4} />
      </div>

      <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Приёмы по дням (последние 7 дней)</CardTitle>
          </CardHeader>
          <CardContent className="h-[300px]">
             {visitsByDay && (
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={visitsByDay} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="hsl(var(--border))" />
                  <XAxis dataKey="date" tickFormatter={(val) => format(new Date(val), "d MMM", { locale: ru })} stroke="hsl(var(--muted-foreground))" fontSize={12} />
                  <YAxis stroke="hsl(var(--muted-foreground))" fontSize={12} allowDecimals={false} />
                  <Tooltip 
                    contentStyle={{ backgroundColor: "hsl(var(--card))", borderColor: "hsl(var(--border))", borderRadius: "8px" }}
                    labelFormatter={(val) => format(new Date(val), "d MMMM yyyy", { locale: ru })}
                  />
                  <Bar dataKey="count" fill="hsl(var(--primary))" radius={[4, 4, 0, 0]}>
                    {visitsByDay.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={index === visitsByDay.length - 1 ? "hsl(var(--primary))" : "hsl(var(--primary)/0.6)"} />
                    ))}
                  </Bar>
                </BarChart>
              </ResponsiveContainer>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Частые заболевания</CardTitle>
          </CardHeader>
          <CardContent className="h-[300px]">
            {topDiseases && (
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={topDiseases} layout="vertical" margin={{ top: 0, right: 10, left: 0, bottom: 0 }}>
                  <CartesianGrid strokeDasharray="3 3" horizontal={false} stroke="hsl(var(--border))" />
                  <XAxis type="number" stroke="hsl(var(--muted-foreground))" fontSize={12} allowDecimals={false} />
                  <YAxis dataKey="diseaseName" type="category" width={120} stroke="hsl(var(--muted-foreground))" fontSize={12} />
                  <Tooltip 
                    contentStyle={{ backgroundColor: "hsl(var(--card))", borderColor: "hsl(var(--border))", borderRadius: "8px" }}
                  />
                  <Bar dataKey="count" fill="hsl(var(--chart-2))" radius={[0, 4, 4, 0]} />
                </BarChart>
              </ResponsiveContainer>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Загруженность врачей</CardTitle>
          </CardHeader>
          <CardContent className="h-[300px]">
            {doctorLoad && (
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={doctorLoad} layout="vertical" margin={{ top: 0, right: 10, left: 0, bottom: 0 }}>
                  <CartesianGrid strokeDasharray="3 3" horizontal={false} stroke="hsl(var(--border))" />
                  <XAxis type="number" stroke="hsl(var(--muted-foreground))" fontSize={12} allowDecimals={false} />
                  <YAxis dataKey="doctorName" type="category" width={120} stroke="hsl(var(--muted-foreground))" fontSize={12} />
                  <Tooltip 
                    contentStyle={{ backgroundColor: "hsl(var(--card))", borderColor: "hsl(var(--border))", borderRadius: "8px" }}
                  />
                  <Bar dataKey="count" fill="hsl(var(--chart-3))" radius={[0, 4, 4, 0]} />
                </BarChart>
              </ResponsiveContainer>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Недавняя активность</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {recentActivity?.slice(0, 5).map((activity: any, i: number) => (
                <motion.div key={activity.id || i} initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: i * 0.1 }} className="flex items-start gap-4">
                  <div className="rounded-full p-2 bg-primary/10 text-primary">
                    <ActivityIcon className="w-4 h-4" />
                  </div>
                  <div className="min-w-0">
                    <p className="text-sm font-medium truncate">
                      {activity.patientName} — {activity.doctorName}
                    </p>
                    <p className="text-xs text-muted-foreground truncate">
                      {activity.diseaseName ?? "Без диагноза"} · {format(new Date(activity.visitDate), "dd MMM HH:mm", { locale: ru })}
                    </p>
                  </div>
                </motion.div>
              ))}
              {!recentActivity?.length && (
                <div className="text-center py-8 text-muted-foreground text-sm">
                  Нет недавней активности
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}

function StatCard({ title, value, icon: Icon, delay }: any) {
  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.9 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{ delay }}
    >
      <Card className="hover-elevate transition-all border-none shadow-sm bg-card text-card-foreground">
        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle className="text-sm font-medium text-muted-foreground">{title}</CardTitle>
          <div className="p-2 bg-primary/10 rounded-full">
            <Icon className="h-4 w-4 text-primary" />
          </div>
        </CardHeader>
        <CardContent>
          <div className="text-3xl font-bold">{value ?? "..."}</div>
        </CardContent>
      </Card>
    </motion.div>
  );
}
