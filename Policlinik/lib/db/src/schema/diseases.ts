import { pgTable, serial, text, boolean, timestamp } from "drizzle-orm/pg-core";

export const diseasesTable = pgTable("diseases", {
  id: serial("id").primaryKey(),
  name: text("name").notNull(),
  code: text("code").notNull(),
  category: text("category").notNull(),
  severity: text("severity").notNull(),
  description: text("description").notNull(),
  contagious: boolean("contagious").notNull().default(false),
  createdAt: timestamp("created_at", { withTimezone: true }).defaultNow().notNull(),
});

export type Disease = typeof diseasesTable.$inferSelect;
export type InsertDisease = typeof diseasesTable.$inferInsert;
