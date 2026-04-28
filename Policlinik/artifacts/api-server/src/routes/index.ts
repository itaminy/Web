import { Router, type IRouter } from "express";
import healthRouter from "./health";
import doctorsRouter from "./doctors";
import patientsRouter from "./patients";
import diseasesRouter from "./diseases";
import visitsRouter from "./visits";
import statsRouter from "./stats";

const router: IRouter = Router();

router.use(healthRouter);
router.use(doctorsRouter);
router.use(patientsRouter);
router.use(diseasesRouter);
router.use(visitsRouter);
router.use(statsRouter);

export default router;
