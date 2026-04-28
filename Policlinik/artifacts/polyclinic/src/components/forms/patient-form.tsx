import { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import { useCreatePatient, useUpdatePatient, getListPatientsQueryKey } from "@workspace/api-client-react";
import { useQueryClient } from "@tanstack/react-query";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { useToast } from "@/hooks/use-toast";

const schema = z.object({
  fullName: z.string().min(1, "Обязательное поле"),
  gender: z.enum(["male", "female"]),
  birthDate: z.string().min(1, "Обязательное поле"),
  phone: z.string().min(1, "Обязательное поле"),
  address: z.string().min(1, "Обязательное поле"),
  insuranceNumber: z.string().min(1, "Обязательное поле"),
});

export function PatientForm({ open, onOpenChange, patient }: { open: boolean; onOpenChange: (open: boolean) => void; patient?: any }) {
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const createMutation = useCreatePatient();
  const updateMutation = useUpdatePatient();

  const form = useForm<z.infer<typeof schema>>({
    resolver: zodResolver(schema),
    defaultValues: patient ? {
      ...patient,
      birthDate: patient.birthDate.split("T")[0],
    } : {
      fullName: "",
      gender: "male",
      birthDate: "",
      phone: "",
      address: "",
      insuranceNumber: "",
    },
  });

  const onSubmit = async (values: z.infer<typeof schema>) => {
    try {
      const payload = {
        ...values,
        birthDate: new Date(values.birthDate).toISOString(),
      };

      if (patient) {
        await updateMutation.mutateAsync({ id: patient.id, data: payload });
        toast({ title: "Пациент обновлен" });
      } else {
        await createMutation.mutateAsync({ data: payload });
        toast({ title: "Пациент добавлен" });
      }
      queryClient.invalidateQueries({ queryKey: getListPatientsQueryKey() });
      onOpenChange(false);
      form.reset();
    } catch (e) {
      toast({ title: "Ошибка", variant: "destructive" });
    }
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>{patient ? "Редактировать пациента" : "Добавить пациента"}</DialogTitle>
        </DialogHeader>
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <FormField control={form.control} name="fullName" render={({ field }) => (
              <FormItem><FormLabel>ФИО</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
            )} />
            <div className="grid grid-cols-2 gap-4">
              <FormField control={form.control} name="gender" render={({ field }) => (
                <FormItem>
                  <FormLabel>Пол</FormLabel>
                  <Select onValueChange={field.onChange} defaultValue={field.value}>
                    <FormControl><SelectTrigger><SelectValue placeholder="Выберите пол" /></SelectTrigger></FormControl>
                    <SelectContent>
                      <SelectItem value="male">Мужской</SelectItem>
                      <SelectItem value="female">Женский</SelectItem>
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )} />
              <FormField control={form.control} name="birthDate" render={({ field }) => (
                <FormItem><FormLabel>Дата рождения</FormLabel><FormControl><Input type="date" {...field} /></FormControl><FormMessage /></FormItem>
              )} />
            </div>
            <FormField control={form.control} name="phone" render={({ field }) => (
              <FormItem><FormLabel>Телефон</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
            )} />
            <FormField control={form.control} name="address" render={({ field }) => (
              <FormItem><FormLabel>Адрес</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
            )} />
            <FormField control={form.control} name="insuranceNumber" render={({ field }) => (
              <FormItem><FormLabel>Полис ОМС</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
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