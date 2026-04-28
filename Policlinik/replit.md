# Workspace

## Overview

pnpm workspace monorepo using TypeScript. Hosts the **Поликлиника** web app — a polyclinic management system with doctors, patients, diseases and a visit log.

## Stack

- **Monorepo tool**: pnpm workspaces
- **Node.js version**: 24
- **Package manager**: pnpm
- **TypeScript version**: 5.9
- **Frontend**: React 19 + Vite + Tailwind v4 + shadcn/ui + framer-motion + Recharts + wouter + TanStack Query
- **API framework**: Express 5
- **Database**: PostgreSQL + Drizzle ORM
- **Validation**: Zod (`zod/v4`), `drizzle-zod`
- **API codegen**: Orval (from OpenAPI spec)

## Artifacts

- `artifacts/polyclinic` — React/Vite frontend (root path `/`), Russian UI
- `artifacts/api-server` — Express API at `/api`
- `artifacts/mockup-sandbox` — Component preview sandbox

## Domain model

- `doctors` — full name, specialty, cabinet, phone, experience, photo, bio
- `patients` — full name, gender, birth date, phone, address, insurance number
- `diseases` — name, ICD code, category, severity (mild/moderate/severe), description, contagious flag
- `visits` — patient + doctor + (optional) disease, datetime, status (scheduled/completed/cancelled), complaints, prescription

## API surface

CRUD for `/doctors`, `/patients`, `/diseases`, `/visits`, plus stats:
`/stats/overview`, `/stats/visits-by-day`, `/stats/top-diseases`, `/stats/doctor-load`, `/stats/recent-activity`.

## Key Commands

- `pnpm run typecheck` — full typecheck
- `pnpm run build` — typecheck + build all packages
- `pnpm --filter @workspace/api-spec run codegen` — regenerate API hooks and Zod schemas from OpenAPI spec
- `pnpm --filter @workspace/db run push` — push DB schema changes (dev only)
