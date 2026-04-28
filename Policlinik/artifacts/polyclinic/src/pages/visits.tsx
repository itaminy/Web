import { useState } from "react";
import { useListVisits, useDeleteVisit, getListVisitsQueryKey } from "@workspace/api-client-react";
import { useQueryClient } from "@tanstack/react-query";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { Plus, Edit2, Trash2, Filter } from "lucide-react";
import { VisitForm } from "@/components/forms/visit-form";
import { useToast } from "@/hooks/use-toast";
import { format } from "date-fns";
import { ru } from "date-fns/locale";
import emptyImg from "@/assets/images/empty-state.jpg";

export default function Visits() {
  const [statusFilter, setStatusFilter] = useState<string>("all");
  const { data: visits, isLoading } = useListVisits({ 
    status: statusFilter !== "all" ? statusFilter as any : undefined 
  });
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const deleteMutation = useDeleteVisit();

  const [formOpen, setFormOpen] = useState(false);
  const [editingVisit, setEditingVisit] = useState<any>(null);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const statusMap: Record<string, { label: string, variant: "default" | "secondary" | "destructive" | "outline" | "primary" }> = {
    scheduled: { label: "Запланирован", variant: "outline" },
    completed: { label: "Завершён", variant: "default" },
    cancelled: { label: "Отменён", variant: "destructive" },
  };

  const handleEdit = (visit: any) => {
    setEditingVisit(visit);
    setFormOpen(true);
  };

  const handleAdd = () => {
    setEditingVisit(null);
    setFormOpen(true);
  };

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await deleteMutation.mutateAsync({ id: deleteId });
      queryClient.invalidateQueries({ queryKey: getListVisitsQueryKey() });
      toast({ title: "Приём удален" });
    } catch (e) {
      toast({ title: "Ошибка удаления", variant: "destructive" });
    } finally {
      setDeleteId(null);
    }
  };

  return (
    <div className="space-y-6 pb-10">
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 className="text-3xl font-bold">Журнал приёмов</h1>
        <Button onClick={handleAdd} className="gap-2">
          <Plus className="w-4 h-4" /> Записать на приём
        </Button>
      </div>

      <div className="flex w-full max-w-sm items-center space-x-2">
        <Select value={statusFilter} onValueChange={setStatusFilter}>
          <SelectTrigger className="bg-card w-[200px]">
            <div className="flex items-center gap-2">
              <Filter className="w-4 h-4 text-muted-foreground" />
              <SelectValue placeholder="Статус" />
            </div>
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">Все статусы</SelectItem>
            <SelectItem value="scheduled">Запланированы</SelectItem>
            <SelectItem value="completed">Завершены</SelectItem>
            <SelectItem value="cancelled">Отменены</SelectItem>
          </SelectContent>
        </Select>
      </div>

      {!isLoading && visits?.length === 0 ? (
        <div className="flex flex-col items-center justify-center p-12 text-center border rounded-xl border-dashed bg-card/50">
          <img src={emptyImg} alt="Empty" className="w-32 h-32 object-cover rounded-full mb-4 opacity-50 grayscale" />
          <h3 className="text-lg font-medium">Приёмы не найдены</h3>
          <p className="text-muted-foreground mt-2 max-w-sm">Журнал приёмов пуст по заданному фильтру.</p>
          <Button variant="outline" className="mt-4" onClick={handleAdd}>Записать на приём</Button>
        </div>
      ) : (
        <div className="border rounded-xl bg-card overflow-hidden">
          <Table>
            <TableHeader className="bg-muted/50">
              <TableRow>
                <TableHead>Дата и время</TableHead>
                <TableHead>Пациент</TableHead>
                <TableHead>Врач</TableHead>
                <TableHead>Диагноз</TableHead>
                <TableHead>Статус</TableHead>
                <TableHead className="w-[100px]"></TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {visits?.map((v: any) => (
                <TableRow key={v.id} className="hover:bg-muted/20 transition-colors">
                  <TableCell className="font-medium whitespace-nowrap">
                    {format(new Date(v.visitDate), "dd MMM yyyy", { locale: ru })}
                    <div className="text-muted-foreground text-sm font-normal">
                      {format(new Date(v.visitDate), "HH:mm")}
                    </div>
                  </TableCell>
                  <TableCell>{v.patientName || `Пациент #${v.patientId}`}</TableCell>
                  <TableCell>{v.doctorName ? `${v.doctorName} (${v.doctorSpecialty})` : `Врач #${v.doctorId}`}</TableCell>
                  <TableCell className="text-muted-foreground text-sm max-w-[200px] truncate" title={v.diseaseName || "Нет"}>
                    {v.diseaseName || "—"}
                  </TableCell>
                  <TableCell>
                    <Badge variant={statusMap[v.status]?.variant as any || "default"} className="font-normal shadow-none">
                      {statusMap[v.status]?.label}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div className="flex justify-end gap-1">
                      <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => handleEdit(v)}>
                        <Edit2 className="w-4 h-4 text-muted-foreground" />
                      </Button>
                      <Button variant="ghost" size="icon" className="h-8 w-8 hover:text-destructive hover:bg-destructive/10" onClick={() => setDeleteId(v.id)}>
                        <Trash2 className="w-4 h-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>
      )}

      <VisitForm open={formOpen} onOpenChange={setFormOpen} visit={editingVisit} />

      <AlertDialog open={!!deleteId} onOpenChange={(open) => !open && setDeleteId(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Удалить запись на приём?</AlertDialogTitle>
            <AlertDialogDescription>
              Это действие нельзя отменить.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Отмена</AlertDialogCancel>
            <AlertDialogAction onClick={handleDelete} className="bg-destructive hover:bg-destructive/90">Удалить</AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
