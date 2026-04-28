import { Router, type IRouter } from "express";
import {
  db,
  visitsTable,
  doctorsTable,
  patientsTable,
  diseasesTable,
} from "@workspace/db";
import { sql, desc, eq, gte } from "drizzle-orm";
import { GetVisitsByDayQueryParams } from "@workspace/api-zod";

const router: IRouter = Router();

router.get("/stats/overview", async (_req, res) => {
  const startOfDay = new Date();
  startOfDay.setHours(0, 0, 0, 0);
  const startOfWeek = new Date(startOfDay);
  startOfWeek.setDate(startOfWeek.getDate() - 7);

  const [{ count: totalDoctors }] = await db
    .select({ count: sql<number>`cast(count(*) as int)` })
    .from(doctorsTable);
  const [{ count: totalPatients }] = await db
    .select({ count: sql<number>`cast(count(*) as int)` })
    .from(patientsTable);
  const [{ count: totalDiseases }] = await db
    .select({ count: sql<number>`cast(count(*) as int)` })
    .from(diseasesTable);
  const [{ count: totalVisits }] = await db
    .select({ count: sql<number>`cast(count(*) as int)` })
    .from(visitsTable);
  const [{ count: visitsToday }] = await db
    .select({ count: sql<number>`cast(count(*) as int)` })
    .from(visitsTable)
    .where(gte(visitsTable.visitDate, startOfDay));
  const [{ count: visitsThisWeek }] = await db
    .select({ count: sql<number>`cast(count(*) as int)` })
    .from(visitsTable)
    .where(gte(visitsTable.visitDate, startOfWeek));
  const [{ count: scheduledVisits }] = await db
    .select({ count: sql<number>`cast(count(*) as int)` })
    .from(visitsTable)
    .where(eq(visitsTable.status, "scheduled"));
  const [{ count: completedVisits }] = await db
    .select({ count: sql<number>`cast(count(*) as int)` })
    .from(visitsTable)
    .where(eq(visitsTable.status, "completed"));

  res.json({
    totalDoctors,
    totalPatients,
    totalDiseases,
    totalVisits,
    visitsToday,
    visitsThisWeek,
    scheduledVisits,
    completedVisits,
  });
});

router.get("/stats/visits-by-day", async (req, res) => {
  const params = GetVisitsByDayQueryParams.parse(req.query);
  const days = params.days ?? 14;
  const since = new Date();
  since.setHours(0, 0, 0, 0);
  since.setDate(since.getDate() - (days - 1));

  const rows = await db
    .select({
      date: sql<string>`to_char(date_trunc('day', ${visitsTable.visitDate}), 'YYYY-MM-DD')`,
      count: sql<number>`cast(count(*) as int)`,
    })
    .from(visitsTable)
    .where(gte(visitsTable.visitDate, since))
    .groupBy(sql`date_trunc('day', ${visitsTable.visitDate})`);

  const map = new Map(rows.map((r) => [r.date, r.count]));
  const out: { date: string; count: number }[] = [];
  for (let i = 0; i < days; i++) {
    const d = new Date(since);
    d.setDate(since.getDate() + i);
    const key = d.toISOString().slice(0, 10);
    out.push({ date: key, count: map.get(key) ?? 0 });
  }
  res.json(out);
});

router.get("/stats/top-diseases", async (_req, res) => {
  const rows = await db
    .select({
      diseaseId: diseasesTable.id,
      diseaseName: diseasesTable.name,
      category: diseasesTable.category,
      count: sql<number>`cast(count(${visitsTable.id}) as int)`,
    })
    .from(diseasesTable)
    .leftJoin(visitsTable, eq(visitsTable.diseaseId, diseasesTable.id))
    .groupBy(diseasesTable.id, diseasesTable.name, diseasesTable.category)
    .orderBy(desc(sql`count(${visitsTable.id})`))
    .limit(8);
  res.json(rows);
});

router.get("/stats/doctor-load", async (_req, res) => {
  const rows = await db
    .select({
      doctorId: doctorsTable.id,
      doctorName: doctorsTable.fullName,
      specialty: doctorsTable.specialty,
      count: sql<number>`cast(count(${visitsTable.id}) as int)`,
    })
    .from(doctorsTable)
    .leftJoin(visitsTable, eq(visitsTable.doctorId, doctorsTable.id))
    .groupBy(doctorsTable.id, doctorsTable.fullName, doctorsTable.specialty)
    .orderBy(desc(sql`count(${visitsTable.id})`));
  res.json(rows);
});

router.get("/stats/recent-activity", async (_req, res) => {
  const rows = await db
    .select({
      id: visitsTable.id,
      doctorId: visitsTable.doctorId,
      patientId: visitsTable.patientId,
      diseaseId: visitsTable.diseaseId,
      visitDate: visitsTable.visitDate,
      status: visitsTable.status,
      complaints: visitsTable.complaints,
      prescription: visitsTable.prescription,
      createdAt: visitsTable.createdAt,
      doctorName: doctorsTable.fullName,
      doctorSpecialty: doctorsTable.specialty,
      patientName: patientsTable.fullName,
      diseaseName: diseasesTable.name,
    })
    .from(visitsTable)
    .leftJoin(doctorsTable, eq(visitsTable.doctorId, doctorsTable.id))
    .leftJoin(patientsTable, eq(visitsTable.patientId, patientsTable.id))
    .leftJoin(diseasesTable, eq(visitsTable.diseaseId, diseasesTable.id))
    .orderBy(desc(visitsTable.createdAt))
    .limit(10);

  res.json(
    rows.map((r) => ({
      ...r,
      visitDate: r.visitDate.toISOString(),
      createdAt: r.createdAt.toISOString(),
      doctorName: r.doctorName ?? "Удалён",
      doctorSpecialty: r.doctorSpecialty ?? "—",
      patientName: r.patientName ?? "Удалён",
    })),
  );
});

export default router;
