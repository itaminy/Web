import { pgTable, serial, text, integer, timestamp } from "drizzle-orm/pg-core";

export const doctorsTable = pgTable("doctors", {
  id: serial("id").primaryKey(),
  fullName: text("full_name").notNull(),
  specialty: text("specialty").notNull(),
  cabinet: text("cabinet").notNull(),
  phone: text("phone").notNull(),
  experienceYears: integer("experience_years").notNull(),
  photoUrl: text("photo_url"),
  bio: text("bio"),
  createdAt: timestamp("created_at", { withTimezone: true }).defaultNow().notNull(),
});

export type Doctor = typeof doctorsTable.$inferSelect;
export type InsertDoctor = typeof doctorsTable.$inferInsert;
