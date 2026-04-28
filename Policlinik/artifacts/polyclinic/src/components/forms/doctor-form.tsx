import { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import { useCreateDoctor, useUpdateDoctor, getListDoctorsQueryKey } from "@workspace/api-client-react";
import { useQueryClient } from "@tanstack/react-query";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { useToast } from "@/hooks/use-toast";

const schema = z.object({
  fullName: z.string().min(1, "Обязательное поле"),
  specialty: z.string().min(1, "Обязательное поле"),
  cabinet: z.string().min(1, "Обязательное поле"),
  phone: z.string().min(1, "Обязательное поле"),
  experienceYears: z.coerce.number().min(0, "Не может быть меньше 0"),
  photoUrl: z.string().url("Неверный URL").optional().or(z.literal("")),
  bio: z.string().optional(),
});

export function DoctorForm({ open, onOpenChange, doctor }: { open: boolean; onOpenChange: (open: boolean) => void; doctor?: any }) {
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const createMutation = useCreateDoctor();
  const updateMutation = useUpdateDoctor();

  const form = useForm<z.infer<typeof schema>>({
    resolver: zodResolver(schema),
    defaultValues: doctor || {
      fullName: "",
      specialty: "",
      cabinet: "",
      phone: "",
      experienceYears: 0,
      photoUrl: "",
      bio: "",
    },
  });

  const onSubmit = async (values: z.infer<typeof schema>) => {
    try {
      const payload = {
        ...values,
        photoUrl: values.photoUrl || null,
        bio: values.bio || null,
      };

      if (doctor) {
        await updateMutation.mutateAsync({ id: doctor.id, data: payload });
        toast({ title: "Врач обновлен" });
      } else {
        await createMutation.mutateAsync({ data: payload });
        toast({ title: "Врач добавлен" });
      }
      queryClient.invalidateQueries({ queryKey: getListDoctorsQueryKey() });
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
          <DialogTitle>{doctor ? "Редактировать врача" : "Добавить врача"}</DialogTitle>
        </DialogHeader>
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <FormField control={form.control} name="fullName" render={({ field }) => (
              <FormItem><FormLabel>ФИО</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
            )} />
            <FormField control={form.control} name="specialty" render={({ field }) => (
              <FormItem><FormLabel>Специальность</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
            )} />
            <div className="grid grid-cols-2 gap-4">
              <FormField control={form.control} name="cabinet" render={({ field }) => (
                <FormItem><FormLabel>Кабинет</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
              )} />
              <FormField control={form.control} name="experienceYears" render={({ field }) => (
                <FormItem><FormLabel>Стаж (лет)</FormLabel><FormControl><Input type="number" {...field} /></FormControl><FormMessage /></FormItem>
              )} />
            </div>
            <FormField control={form.control} name="phone" render={({ field }) => (
              <FormItem><FormLabel>Телефон</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
            )} />
            <FormField control={form.control} name="photoUrl" render={({ field }) => (
              <FormItem><FormLabel>URL фото (необязательно)</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
            )} />
            <FormField control={form.control} name="bio" render={({ field }) => (
              <FormItem><FormLabel>О себе</FormLabel><FormControl><Textarea {...field} /></FormControl><FormMessage /></FormItem>
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