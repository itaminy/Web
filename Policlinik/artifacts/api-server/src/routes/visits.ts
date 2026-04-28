import { Router, type IRouter } from "express";
import {
  db,
  visitsTable,
  doctorsTable,
  patientsTable,
  diseasesTable,
} from "@workspace/db";
import { and, eq, gte, lte, desc } from "drizzle-orm";
import {
  CreateVisitBody,
  UpdateVisitBody,
  ListVisitsQueryParams,
  GetVisitParams,
} from "@workspace/api-zod";

const router: IRouter = Router();

const baseQuery = () =>
  db
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
    .leftJoin(diseasesTable, eq(visitsTable.diseaseId, diseasesTable.id));

function serializeRow(row: Awaited<ReturnType<typeof baseQuery>>[number]) {
  return {
    ...row,
    visitDate: row.visitDate.toISOString(),
    createdAt: row.createdAt.toISOString(),
    doctorName: row.doctorName ?? "Удалён",
    doctorSpecialty: row.doctorSpecialty ?? "—",
    patientName: row.patientName ?? "Удалён",
  };
}

router.get("/visits", async (req, res) => {
  const params = ListVisitsQueryParams.parse(req.query);
  const conds = [];
  if (params.doctorId) conds.push(eq(visitsTable.doctorId, params.doctorId));
  if (params.patientId) conds.push(eq(visitsTable.patientId, params.patientId));
  if (params.diseaseId) conds.push(eq(visitsTable.diseaseId, params.diseaseId));
  if (params.status) conds.push(eq(visitsTable.status, params.status));
  if (params.dateFrom)
    conds.push(gte(visitsTable.visitDate, new Date(params.dateFrom)));
  if (params.dateTo)
    conds.push(lte(visitsTable.visitDate, new Date(params.dateTo + "T23:59:59")));

  const rows = await baseQuery()
    .where(conds.length ? and(...conds) : undefined)
    .orderBy(desc(visitsTable.visitDate));
  res.json(rows.map(serializeRow));
});

router.get("/visits/:id", async (req, res) => {
  const { id } = GetVisitParams.parse({ id: Number(req.params.id) });
  const [row] = await baseQuery().where(eq(visitsTable.id, id));
  if (!row) {
    res.status(404).json({ error: "Not found" });
    return;
  }
  res.json(serializeRow(row));
});

router.post("/visits", async (req, res) => {
  const body = CreateVisitBody.parse(req.body);
  const [row] = await db
    .insert(visitsTable)
    .values({ ...body, visitDate: new Date(body.visitDate) })
    .returning();
  res.status(201).json({
    ...row,
    visitDate: row.visitDate.toISOString(),
    createdAt: row.createdAt.toISOString(),
  });
});

router.put("/visits/:id", async (req, res) => {
  const { id } = GetVisitParams.parse({ id: Number(req.params.id) });
  const body = UpdateVisitBody.parse(req.body);
  const [row] = await db
    .update(visitsTable)
    .set({ ...body, visitDate: new Date(body.visitDate) })
    .where(eq(visitsTable.id, id))
    .returning();
  if (!row) {
    res.status(404).json({ error: "Not found" });
    return;
  }
  res.json({
    ...row,
    visitDate: row.visitDate.toISOString(),
    createdAt: row.createdAt.toISOString(),
  });
});

router.delete("/visits/:id", async (req, res) => {
  const { id } = GetVisitParams.parse({ id: Number(req.params.id) });
  await db.delete(visitsTable).where(eq(visitsTable.id, id));
  res.status(204).end();
});

export default router;
