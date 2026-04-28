import { useState } from "react";
import { useListPatients, useDeletePatient, getListPatientsQueryKey } from "@workspace/api-client-react";
import { useQueryClient } from "@tanstack/react-query";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { Search, Plus, Edit2, Trash2 } from "lucide-react";
import { PatientForm } from "@/components/forms/patient-form";
import { useToast } from "@/hooks/use-toast";
import { format } from "date-fns";
import { ru } from "date-fns/locale";
import emptyImg from "@/assets/images/empty-state.jpg";
import { useDebounce } from "@/hooks/use-debounce";

export default function Patients() {
  const [search, setSearch] = useState("");
  const debouncedSearch = useDebounce(search, 300);
  const { data: patients, isLoading } = useListPatients({ search: debouncedSearch || undefined });
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const deleteMutation = useDeletePatient();

  const [formOpen, setFormOpen] = useState(false);
  const [editingPatient, setEditingPatient] = useState<any>(null);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const handleEdit = (patient: any) => {
    setEditingPatient(patient);
    setFormOpen(true);
  };

  const handleAdd = () => {
    setEditingPatient(null);
    setFormOpen(true);
  };

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await deleteMutation.mutateAsync({ id: deleteId });
      queryClient.invalidateQueries({ queryKey: getListPatientsQueryKey() });
      toast({ title: "Пациент удален" });
    } catch (e) {
      toast({ title: "Ошибка удаления", variant: "destructive" });
    } finally {
      setDeleteId(null);
    }
  };

  return (
    <div className="space-y-6 pb-10">
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 className="text-3xl font-bold">Пациенты</h1>
        <Button onClick={handleAdd} className="gap-2">
          <Plus className="w-4 h-4" /> Добавить пациента
        </Button>
      </div>

      <div className="flex w-full max-w-sm items-center space-x-2">
        <div className="relative w-full">
          <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
          <Input 
            placeholder="Поиск по ФИО..." 
            className="pl-8 bg-card" 
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>
      </div>

      {!isLoading && patients?.length === 0 ? (
        <div className="flex flex-col items-center justify-center p-12 text-center border rounded-xl border-dashed bg-card/50">
          <img src={emptyImg} alt="Empty" className="w-32 h-32 object-cover rounded-full mb-4 opacity-50 grayscale" />
          <h3 className="text-lg font-medium">Пациенты не найдены</h3>
          <p className="text-muted-foreground mt-2 max-w-sm">Измените параметры поиска или добавьте нового пациента.</p>
          <Button variant="outline" className="mt-4" onClick={handleAdd}>Добавить пациента</Button>
        </div>
      ) : (
        <div className="border rounded-xl bg-card overflow-hidden">
          <Table>
            <TableHeader className="bg-muted/50">
              <TableRow>
                <TableHead>ФИО</TableHead>
                <TableHead>Пол</TableHead>
                <TableHead>Дата рождения</TableHead>
                <TableHead>Телефон</TableHead>
                <TableHead>Полис ОМС</TableHead>
                <TableHead className="w-[100px]"></TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {patients?.map((p) => (
                <TableRow key={p.id} className="hover:bg-muted/20 transition-colors">
                  <TableCell className="font-medium">{p.fullName}</TableCell>
                  <TableCell>{p.gender === "male" ? "Мужской" : "Женский"}</TableCell>
                  <TableCell>{format(new Date(p.birthDate), "dd MMM yyyy", { locale: ru })}</TableCell>
                  <TableCell>{p.phone}</TableCell>
                  <TableCell className="text-muted-foreground font-mono text-sm">{p.insuranceNumber}</TableCell>
                  <TableCell>
                    <div className="flex justify-end gap-1">
                      <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => handleEdit(p)}>
                        <Edit2 className="w-4 h-4 text-muted-foreground" />
                      </Button>
                      <Button variant="ghost" size="icon" className="h-8 w-8 hover:text-destructive hover:bg-destructive/10" onClick={() => setDeleteId(p.id)}>
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

      <PatientForm open={formOpen} onOpenChange={setFormOpen} patient={editingPatient} />

      <AlertDialog open={!!deleteId} onOpenChange={(open) => !open && setDeleteId(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Удалить пациента?</AlertDialogTitle>
            <AlertDialogDescription>
              Все данные пациента будут удалены. Это действие нельзя отменить.
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
