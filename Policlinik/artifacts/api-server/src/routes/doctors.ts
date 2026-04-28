import { Router, type IRouter } from "express";
import { db, doctorsTable } from "@workspace/db";
import { and, eq, ilike } from "drizzle-orm";
import {
  CreateDoctorBody,
  UpdateDoctorBody,
  ListDoctorsQueryParams,
  GetDoctorParams,
} from "@workspace/api-zod";

const router: IRouter = Router();

router.get("/doctors", async (req, res) => {
  const params = ListDoctorsQueryParams.parse(req.query);
  const conds = [];
  if (params.search) {
    conds.push(ilike(doctorsTable.fullName, `%${params.search}%`));
  }
  if (params.specialty) {
    conds.push(eq(doctorsTable.specialty, params.specialty));
  }
  const rows = await db
    .select()
    .from(doctorsTable)
    .where(conds.length ? and(...conds) : undefined)
    .orderBy(doctorsTable.fullName);
  res.json(
    rows.map((r) => ({ ...r, createdAt: r.createdAt.toISOString() })),
  );
});

router.get("/doctors/:id", async (req, res) => {
  const { id } = GetDoctorParams.parse({ id: Number(req.params.id) });
  const [row] = await db
    .select()
    .from(doctorsTable)
    .where(eq(doctorsTable.id, id));
  if (!row) {
    res.status(404).json({ error: "Not found" });
    return;
  }
  res.json({ ...row, createdAt: row.createdAt.toISOString() });
});

router.post("/doctors", async (req, res) => {
  const body = CreateDoctorBody.parse(req.body);
  const [row] = await db.insert(doctorsTable).values(body).returning();
  res.status(201).json({ ...row, createdAt: row.createdAt.toISOString() });
});

router.put("/doctors/:id", async (req, res) => {
  const { id } = GetDoctorParams.parse({ id: Number(req.params.id) });
  const body = UpdateDoctorBody.parse(req.body);
  const [row] = await db
    .update(doctorsTable)
    .set(body)
    .where(eq(doctorsTable.id, id))
    .returning();
  if (!row) {
    res.status(404).json({ error: "Not found" });
    return;
  }
  res.json({ ...row, createdAt: row.createdAt.toISOString() });
});

router.delete("/doctors/:id", async (req, res) => {
  const { id } = GetDoctorParams.parse({ id: Number(req.params.id) });
  await db.delete(doctorsTable).where(eq(doctorsTable.id, id));
  res.status(204).end();
});

export default router;
