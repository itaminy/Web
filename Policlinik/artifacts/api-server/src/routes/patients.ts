import { Router, type IRouter } from "express";
import { db, patientsTable } from "@workspace/db";
import { and, eq, ilike } from "drizzle-orm";
import {
  CreatePatientBody,
  UpdatePatientBody,
  ListPatientsQueryParams,
  GetPatientParams,
} from "@workspace/api-zod";

const router: IRouter = Router();

router.get("/patients", async (req, res) => {
  const params = ListPatientsQueryParams.parse(req.query);
  const conds = [];
  if (params.search) {
    conds.push(ilike(patientsTable.fullName, `%${params.search}%`));
  }
  if (params.gender) {
    conds.push(eq(patientsTable.gender, params.gender));
  }
  const rows = await db
    .select()
    .from(patientsTable)
    .where(conds.length ? and(...conds) : undefined)
    .orderBy(patientsTable.fullName);
  res.json(
    rows.map((r) => ({ ...r, createdAt: r.createdAt.toISOString() })),
  );
});

router.get("/patients/:id", async (req, res) => {
  const { id } = GetPatientParams.parse({ id: Number(req.params.id) });
  const [row] = await db
    .select()
    .from(patientsTable)
    .where(eq(patientsTable.id, id));
  if (!row) {
    res.status(404).json({ error: "Not found" });
    return;
  }
  res.json({ ...row, createdAt: row.createdAt.toISOString() });
});

function toDateString(d: Date): string {
  return d.toISOString().slice(0, 10);
}

router.post("/patients", async (req, res) => {
  const body = CreatePatientBody.parse(req.body);
  const [row] = await db
    .insert(patientsTable)
    .values({ ...body, birthDate: toDateString(body.birthDate) })
    .returning();
  res.status(201).json({ ...row, createdAt: row.createdAt.toISOString() });
});

router.put("/patients/:id", async (req, res) => {
  const { id } = GetPatientParams.parse({ id: Number(req.params.id) });
  const body = UpdatePatientBody.parse(req.body);
  const [row] = await db
    .update(patientsTable)
    .set({ ...body, birthDate: toDateString(body.birthDate) })
    .where(eq(patientsTable.id, id))
    .returning();
  if (!row) {
    res.status(404).json({ error: "Not found" });
    return;
  }
  res.json({ ...row, createdAt: row.createdAt.toISOString() });
});

router.delete("/patients/:id", async (req, res) => {
  const { id } = GetPatientParams.parse({ id: Number(req.params.id) });
  await db.delete(patientsTable).where(eq(patientsTable.id, id));
  res.status(204).end();
});

export default router;
