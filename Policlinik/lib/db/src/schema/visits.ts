import { pgTable, serial, text, integer, timestamp } from "drizzle-orm/pg-core";
import { doctorsTable } from "./doctors";
import { patientsTable } from "./patients";
import { diseasesTable } from "./diseases";

export const visitsTable = pgTable("visits", {
  id: serial("id").primaryKey(),
  doctorId: integer("doctor_id")
    .notNull()
    .references(() => doctorsTable.id, { onDelete: "cascade" }),
  patientId: integer("patient_id")
    .notNull()
    .references(() => patientsTable.id, { onDelete: "cascade" }),
  diseaseId: integer("disease_id").references(() => diseasesTable.id, {
    onDelete: "set null",
  }),
  visitDate: timestamp("visit_date", { withTimezone: true }).notNull(),
  status: text("status").notNull(),
  complaints: text("complaints").notNull(),
  prescription: text("prescription"),
  createdAt: timestamp("created_at", { withTimezone: true }).defaultNow().notNull(),
});

export type Visit = typeof visitsTable.$inferSelect;
export type InsertVisit = typeof visitsTable.$inferInsert;
