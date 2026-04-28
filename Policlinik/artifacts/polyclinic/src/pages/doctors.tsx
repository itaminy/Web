import { useState } from "react";
import { useListDoctors, useDeleteDoctor, getListDoctorsQueryKey } from "@workspace/api-client-react";
import { useQueryClient } from "@tanstack/react-query";
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { motion } from "framer-motion";
import { Search, Plus, Edit2, Trash2 } from "lucide-react";
import { DoctorForm } from "@/components/forms/doctor-form";
import { useToast } from "@/hooks/use-toast";
import defaultAvatar from "@/assets/images/doctor-avatar.jpg";
import emptyImg from "@/assets/images/empty-state.jpg";
import { useDebounce } from "@/hooks/use-debounce";

export default function Doctors() {
  const [search, setSearch] = useState("");
  const debouncedSearch = useDebounce(search, 300);
  const { data: doctors, isLoading } = useListDoctors({ search: debouncedSearch || undefined });
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const deleteMutation = useDeleteDoctor();

  const [formOpen, setFormOpen] = useState(false);
  const [editingDoctor, setEditingDoctor] = useState<any>(null);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const handleEdit = (doctor: any) => {
    setEditingDoctor(doctor);
    setFormOpen(true);
  };

  const handleAdd = () => {
    setEditingDoctor(null);
    setFormOpen(true);
  };

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await deleteMutation.mutateAsync({ id: deleteId });
      queryClient.invalidateQueries({ queryKey: getListDoctorsQueryKey() });
      toast({ title: "Врач удален" });
    } catch (e) {
      toast({ title: "Ошибка удаления", variant: "destructive" });
    } finally {
      setDeleteId(null);
    }
  };

  return (
    <div className="space-y-6 pb-10">
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 className="text-3xl font-bold">Врачи</h1>
        <Button onClick={handleAdd} className="gap-2">
          <Plus className="w-4 h-4" /> Добавить врача
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

      {!isLoading && doctors?.length === 0 ? (
        <div className="flex flex-col items-center justify-center p-12 text-center border rounded-xl border-dashed bg-card/50">
          <img src={emptyImg} alt="Empty" className="w-32 h-32 object-cover rounded-full mb-4 opacity-50 grayscale" />
          <h3 className="text-lg font-medium">Врачи не найдены</h3>
          <p className="text-muted-foreground mt-2 max-w-sm">Ни одного врача не найдено по вашему запросу. Попробуйте изменить параметры поиска или добавить нового.</p>
          <Button variant="outline" className="mt-4" onClick={handleAdd}>Добавить врача</Button>
        </div>
      ) : (
        <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
          {doctors?.map((doc, i) => (
            <motion.div key={doc.id} initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.05 }}>
              <Card className="hover-elevate overflow-hidden border-border/50">
                <CardHeader className="flex flex-row gap-4 items-center bg-card pb-4 border-b border-border/10">
                  <img src={doc.photoUrl || defaultAvatar} className="w-14 h-14 rounded-full object-cover shadow-sm" alt={doc.fullName} />
                  <div>
                    <CardTitle className="text-lg leading-tight">{doc.fullName}</CardTitle>
                    <p className="text-sm font-medium text-primary mt-1">{doc.specialty}</p>
                  </div>
                </CardHeader>
                <CardContent className="pt-4 pb-2 bg-card/50">
                  <div className="text-sm space-y-2">
                    <div className="flex justify-between"><span className="text-muted-foreground">Кабинет:</span> <span className="font-medium">{doc.cabinet}</span></div>
                    <div className="flex justify-between"><span className="text-muted-foreground">Телефон:</span> <span className="font-medium">{doc.phone}</span></div>
                    <div className="flex justify-between"><span className="text-muted-foreground">Стаж:</span> <span className="font-medium">{doc.experienceYears} лет</span></div>
                  </div>
                </CardContent>
                <CardFooter className="bg-card/50 pt-2 flex justify-end gap-2">
                  <Button variant="ghost" size="icon" onClick={() => handleEdit(doc)}>
                    <Edit2 className="w-4 h-4 text-muted-foreground" />
                  </Button>
                  <Button variant="ghost" size="icon" onClick={() => setDeleteId(doc.id)} className="hover:text-destructive hover:bg-destructive/10">
                    <Trash2 className="w-4 h-4" />
                  </Button>
                </CardFooter>
              </Card>
            </motion.div>
          ))}
        </div>
      )}

      <DoctorForm open={formOpen} onOpenChange={setFormOpen} doctor={editingDoctor} />

      <AlertDialog open={!!deleteId} onOpenChange={(open) => !open && setDeleteId(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Удалить врача?</AlertDialogTitle>
            <AlertDialogDescription>
              Это действие нельзя отменить. Вы уверены, что хотите продолжить?
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
