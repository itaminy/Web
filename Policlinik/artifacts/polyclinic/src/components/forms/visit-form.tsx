import { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import { useCreateVisit, useUpdateVisit, getListVisitsQueryKey, useListDoctors, useListPatients, useListDiseases } from "@workspace/api-client-react";
import { useQueryClient } from "@tanstack/react-query";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { useToast } from "@/hooks/use-toast";

const schema = z.object({
  doctorId: z.coerce.number().min(1, "Выберите врача"),
  patientId: z.coerce.number().min(1, "Выберите пациента"),
  diseaseId: z.coerce.number().optional().nullable(),
  visitDate: z.string().min(1, "Обязательное поле"),
  status: z.enum(["scheduled", "completed", "cancelled"]),
  complaints: z.string().min(1, "Обязательное поле"),
  prescription: z.string().optional().nullable(),
});

export function VisitForm({ open, onOpenChange, visit }: { open: boolean; onOpenChange: (open: boolean) => void; visit?: any }) {
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const createMutation = useCreateVisit();
  const updateMutation = useUpdateVisit();

  const { data: doctors } = useListDoctors();
  const { data: patients } = useListPatients();
  const { data: diseases } = useListDiseases();

  const form = useForm<z.infer<typeof schema>>({
    resolver: zodResolver(schema),
    defaultValues: visit ? {
      ...visit,
      visitDate: new Date(visit.visitDate).toISOString().slice(0, 16), // datetime-local format
    } : {
      doctorId: 0,
      patientId: 0,
      diseaseId: null,
      visitDate: "",
      status: "scheduled",
      complaints: "",
      prescription: "",
    },
  });

  const onSubmit = async (values: z.infer<typeof schema>) => {
    try {
      const payload = {
        ...values,
        visitDate: new Date(values.visitDate).toISOString(),
        diseaseId: values.diseaseId || null,
        prescription: values.prescription || null,
      };

      if (visit) {
        await updateMutation.mutateAsync({ id: visit.id, data: payload });
        toast({ title: "Приём обновлен" });
      } else {
        await createMutation.mutateAsync({ data: payload });
        toast({ title: "Приём добавлен" });
      }
      queryClient.invalidateQueries({ queryKey: getListVisitsQueryKey() });
      onOpenChange(false);
      form.reset();
    } catch (e) {
      toast({ title: "Ошибка", variant: "destructive" });
    }
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[425px] overflow-y-auto max-h-[90vh]">
        <DialogHeader>
          <DialogTitle>{visit ? "Редактировать приём" : "Добавить приём"}</DialogTitle>
        </DialogHeader>
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <FormField control={form.control} name="doctorId" render={({ field }) => (
              <FormItem>
                <FormLabel>Врач</FormLabel>
                <Select onValueChange={field.onChange} defaultValue={field.value ? field.value.toString() : ""}>
                  <FormControl><SelectTrigger><SelectValue placeholder="Выберите врача" /></SelectTrigger></FormControl>
                  <SelectContent>
                    {doctors?.map(d => <SelectItem key={d.id} value={d.id.toString()}>{d.fullName} ({d.specialty})</SelectItem>)}
                  </SelectContent>
                </Select>
                <FormMessage />
              </FormItem>
            )} />
            
            <FormField control={form.control} name="patientId" render={({ field }) => (
              <FormItem>
                <FormLabel>Пациент</FormLabel>
                <Select onValueChange={field.onChange} defaultValue={field.value ? field.value.toString() : ""}>
                  <FormControl><SelectTrigger><SelectValue placeholder="Выберите пациента" /></SelectTrigger></FormControl>
                  <SelectContent>
                    {patients?.map(p => <SelectItem key={p.id} value={p.id.toString()}>{p.fullName}</SelectItem>)}
                  </SelectContent>
                </Select>
                <FormMessage />
              </FormItem>
            )} />

            <FormField control={form.control} name="visitDate" render={({ field }) => (
              <FormItem><FormLabel>Дата и время</FormLabel><FormControl><Input type="datetime-local" {...field} /></FormControl><FormMessage /></FormItem>
            )} />

            <div className="grid grid-cols-2 gap-4">
              <FormField control={form.control} name="status" render={({ field }) => (
                <FormItem>
                  <FormLabel>Статус</FormLabel>
                  <Select onValueChange={field.onChange} defaultValue={field.value}>
                    <FormControl><SelectTrigger><SelectValue placeholder="Статус" /></SelectTrigger></FormControl>
                    <SelectContent>
                      <SelectItem value="scheduled">Запланирован</SelectItem>
                      <SelectItem value="completed">Завершён</SelectItem>
                      <SelectItem value="cancelled">Отменён</SelectItem>
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )} />
              
              <FormField control={form.control} name="diseaseId" render={({ field }) => (
                <FormItem>
                  <FormLabel>Диагноз (опц.)</FormLabel>
                  <Select onValueChange={field.onChange} defaultValue={field.value ? field.value.toString() : ""}>
                    <FormControl><SelectTrigger><SelectValue placeholder="Диагноз" /></SelectTrigger></FormControl>
                    <SelectContent>
                      <SelectItem value="0">Нет</SelectItem>
                      {diseases?.map(d => <SelectItem key={d.id} value={d.id.toString()}>{d.name}</SelectItem>)}
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )} />
            </div>

            <FormField control={form.control} name="complaints" render={({ field }) => (
              <FormItem><FormLabel>Жалобы</FormLabel><FormControl><Textarea {...field} /></FormControl><FormMessage /></FormItem>
            )} />
            <FormField control={form.control} name="prescription" render={({ field }) => (
              <FormItem><FormLabel>Назначение (опц.)</FormLabel><FormControl><Textarea {...field} value={field.value || ""} /></FormControl><FormMessage /></FormItem>
            )} />

            <div className="flex justify-end space-x-2 pt-4">
              <Button variant="outline" type="button" onClick={() => onOpenChange(false)}>Отмена</Button>
              <Button type="submit" disabled={createMutation.isPending || updateMutation.isPending}>Сохранить</Button>
            </div>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
}