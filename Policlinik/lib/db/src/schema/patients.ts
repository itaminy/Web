import { pgTable, serial, text, timestamp, date } from "drizzle-orm/pg-core";

export const patientsTable = pgTable("patients", {
  id: serial("id").primaryKey(),
  fullName: text("full_name").notNull(),
  gender: text("gender").notNull(),
  birthDate: date("birth_date").notNull(),
  phone: text("phone").notNull(),
  address: text("address").notNull(),
  insuranceNumber: text("insurance_number").notNull(),
  createdAt: timestamp("created_at", { withTimezone: true }).defaultNow().notNull(),
});

export type Patient = typeof patientsTable.$inferSelect;
export type InsertPatient = typeof patientsTable.$inferInsert;
