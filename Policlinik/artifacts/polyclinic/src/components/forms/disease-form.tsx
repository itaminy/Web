import { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import { useCreateDisease, useUpdateDisease, getListDiseasesQueryKey } from "@workspace/api-client-react";
import { useQueryClient } from "@tanstack/react-query";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Switch } from "@/components/ui/switch";
import { useToast } from "@/hooks/use-toast";

const schema = z.object({
  name: z.string().min(1, "Обязательное поле"),
  code: z.string().min(1, "Обязательное поле"),
  category: z.string().min(1, "Обязательное поле"),
  severity: z.enum(["mild", "moderate", "severe"]),
  description: z.string().min(1, "Обязательное поле"),
  contagious: z.boolean(),
});

export function DiseaseForm({ open, onOpenChange, disease }: { open: boolean; onOpenChange: (open: boolean) => void; disease?: any }) {
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const createMutation = useCreateDisease();
  const updateMutation = useUpdateDisease();

  const form = useForm<z.infer<typeof schema>>({
    resolver: zodResolver(schema),
    defaultValues: disease || {
      name: "",
      code: "",
      category: "",
      severity: "mild",
      description: "",
      contagious: false,
    },
  });

  const onSubmit = async (values: z.infer<typeof schema>) => {
    try {
      if (disease) {
        await updateMutation.mutateAsync({ id: disease.id, data: values });
        toast({ title: "Заболевание обновлено" });
      } else {
        await createMutation.mutateAsync({ data: values });
        toast({ title: "Заболевание добавлено" });
      }
      queryClient.invalidateQueries({ queryKey: getListDiseasesQueryKey() });
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
          <DialogTitle>{disease ? "Редактировать заболевание" : "Добавить заболевание"}</DialogTitle>
        </DialogHeader>
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <FormField control={form.control} name="name" render={({ field }) => (
              <FormItem><FormLabel>Название</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
            )} />
            <div className="grid grid-cols-2 gap-4">
              <FormField control={form.control} name="code" render={({ field }) => (
                <FormItem><FormLabel>Код МКБ</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
              )} />
              <FormField control={form.control} name="category" render={({ field }) => (
                <FormItem><FormLabel>Категория</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
              )} />
            </div>
            <FormField control={form.control} name="severity" render={({ field }) => (
              <FormItem>
                <FormLabel>Тяжесть</FormLabel>
                <Select onValueChange={field.onChange} defaultValue={field.value}>
                  <FormControl><SelectTrigger><SelectValue placeholder="Выберите тяжесть" /></SelectTrigger></FormControl>
                  <SelectContent>
                    <SelectItem value="mild">Лёгкая</SelectItem>
                    <SelectItem value="moderate">Средняя</SelectItem>
                    <SelectItem value="severe">Тяжёлая</SelectItem>
                  </SelectContent>
                </Select>
                <FormMessage />
              </FormItem>
            )} />
            <FormField control={form.control} name="contagious" render={({ field }) => (
              <FormItem className="flex flex-row items-center justify-between rounded-lg border p-4">
                <div className="space-y-0.5">
                  <FormLabel className="text-base">Заразное</FormLabel>
                </div>
                <FormControl>
                  <Switch checked={field.value} onCheckedChange={field.onChange} />
                </FormControl>
              </FormItem>
            )} />
            <FormField control={form.control} name="description" render={({ field }) => (
              <FormItem><FormLabel>Описание</FormLabel><FormControl><Textarea {...field} /></FormControl><FormMessage /></FormItem>
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
