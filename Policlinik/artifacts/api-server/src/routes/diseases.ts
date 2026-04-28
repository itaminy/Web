import { Router, type IRouter } from "express";
import { db, diseasesTable } from "@workspace/db";
import { and, eq, ilike } from "drizzle-orm";
import {
  CreateDiseaseBody,
  UpdateDiseaseBody,
  ListDiseasesQueryParams,
  GetDiseaseParams,
} from "@workspace/api-zod";

const router: IRouter = Router();

router.get("/diseases", async (req, res) => {
  const params = ListDiseasesQueryParams.parse(req.query);
  const conds = [];
  if (params.search) {
    conds.push(ilike(diseasesTable.name, `%${params.search}%`));
  }
  if (params.severity) {
    conds.push(eq(diseasesTable.severity, params.severity));
  }
  const rows = await db
    .select()
    .from(diseasesTable)
    .where(conds.length ? and(...conds) : undefined)
    .orderBy(diseasesTable.name);
  res.json(
    rows.map((r) => ({ ...r, createdAt: r.createdAt.toISOString() })),
  );
});

router.get("/diseases/:id", async (req, res) => {
  const { id } = GetDiseaseParams.parse({ id: Number(req.params.id) });
  const [row] = await db
    .select()
    .from(diseasesTable)
    .where(eq(diseasesTable.id, id));
  if (!row) {
    res.status(404).json({ error: "Not found" });
    return;
  }
  res.json({ ...row, createdAt: row.createdAt.toISOString() });
});

router.post("/diseases", async (req, res) => {
  const body = CreateDiseaseBody.parse(req.body);
  const [row] = await db.insert(diseasesTable).values(body).returning();
  res.status(201).json({ ...row, createdAt: row.createdAt.toISOString() });
});

router.put("/diseases/:id", async (req, res) => {
  const { id } = GetDiseaseParams.parse({ id: Number(req.params.id) });
  const body = UpdateDiseaseBody.parse(req.body);
  const [row] = await db
    .update(diseasesTable)
    .set(body)
    .where(eq(diseasesTable.id, id))
    .returning();
  if (!row) {
    res.status(404).json({ error: "Not found" });
    return;
  }
  res.json({ ...row, createdAt: row.createdAt.toISOString() });
});

router.delete("/diseases/:id", async (req, res) => {
  const { id } = GetDiseaseParams.parse({ id: Number(req.params.id) });
  await db.delete(diseasesTable).where(eq(diseasesTable.id, id));
  res.status(204).end();
});

export default router;
