import { useState } from "react";
import { useListDiseases, useDeleteDisease, getListDiseasesQueryKey } from "@workspace/api-client-react";
import { useQueryClient } from "@tanstack/react-query";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { Search, Plus, Edit2, Trash2 } from "lucide-react";
import { DiseaseForm } from "@/components/forms/disease-form";
import { useToast } from "@/hooks/use-toast";
import emptyImg from "@/assets/images/empty-state.jpg";
import { useDebounce } from "@/hooks/use-debounce";

export default function Diseases() {
  const [search, setSearch] = useState("");
  const debouncedSearch = useDebounce(search, 300);
  const { data: diseases, isLoading } = useListDiseases({ search: debouncedSearch || undefined });
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const deleteMutation = useDeleteDisease();

  const [formOpen, setFormOpen] = useState(false);
  const [editingDisease, setEditingDisease] = useState<any>(null);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const severityMap: Record<string, { label: string, variant: "default" | "secondary" | "destructive" }> = {
    mild: { label: "Лёгкая", variant: "default" },
    moderate: { label: "Средняя", variant: "secondary" },
    severe: { label: "Тяжёлая", variant: "destructive" },
  };

  const handleEdit = (disease: any) => {
    setEditingDisease(disease);
    setFormOpen(true);
  };

  const handleAdd = () => {
    setEditingDisease(null);
    setFormOpen(true);
  };

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await deleteMutation.mutateAsync({ id: deleteId });
      queryClient.invalidateQueries({ queryKey: getListDiseasesQueryKey() });
      toast({ title: "Заболевание удалено" });
    } catch (e) {
      toast({ title: "Ошибка удаления", variant: "destructive" });
    } finally {
      setDeleteId(null);
    }
  };

  return (
    <div className="space-y-6 pb-10">
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 className="text-3xl font-bold">Справочник заболеваний</h1>
        <Button onClick={handleAdd} className="gap-2">
          <Plus className="w-4 h-4" /> Добавить заболевание
        </Button>
      </div>

      <div className="flex w-full max-w-sm items-center space-x-2">
        <div className="relative w-full">
          <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
          <Input 
            placeholder="Поиск по названию или коду..." 
            className="pl-8 bg-card" 
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>
      </div>

      {!isLoading && diseases?.length === 0 ? (
        <div className="flex flex-col items-center justify-center p-12 text-center border rounded-xl border-dashed bg-card/50">
          <img src={emptyImg} alt="Empty" className="w-32 h-32 object-cover rounded-full mb-4 opacity-50 grayscale" />
          <h3 className="text-lg font-medium">Заболевания не найдены</h3>
          <p className="text-muted-foreground mt-2 max-w-sm">Справочник пуст или по вашему запросу ничего не найдено.</p>
          <Button variant="outline" className="mt-4" onClick={handleAdd}>Добавить заболевание</Button>
        </div>
      ) : (
        <div className="border rounded-xl bg-card overflow-hidden">
          <Table>
            <TableHeader className="bg-muted/50">
              <TableRow>
                <TableHead className="w-[100px]">Код</TableHead>
                <TableHead>Название</TableHead>
                <TableHead>Категория</TableHead>
                <TableHead>Тяжесть</TableHead>
                <TableHead>Особенности</TableHead>
                <TableHead className="w-[100px]"></TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {diseases?.map((d) => (
                <TableRow key={d.id} className="hover:bg-muted/20 transition-colors">
                  <TableCell className="font-mono text-sm font-medium text-muted-foreground">{d.code}</TableCell>
                  <TableCell className="font-medium">{d.name}</TableCell>
                  <TableCell>{d.category}</TableCell>
                  <TableCell>
                    <Badge variant={severityMap[d.severity]?.variant || "default"} className="font-normal shadow-none">
                      {severityMap[d.severity]?.label}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    {d.contagious && <Badge variant="destructive" className="font-normal shadow-none">Заразное</Badge>}
                  </TableCell>
                  <TableCell>
                    <div className="flex justify-end gap-1">
                      <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => handleEdit(d)}>
                        <Edit2 className="w-4 h-4 text-muted-foreground" />
                      </Button>
                      <Button variant="ghost" size="icon" className="h-8 w-8 hover:text-destructive hover:bg-destructive/10" onClick={() => setDeleteId(d.id)}>
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

      <DiseaseForm open={formOpen} onOpenChange={setFormOpen} disease={editingDisease} />

      <AlertDialog open={!!deleteId} onOpenChange={(open) => !open && setDeleteId(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Удалить заболевание?</AlertDialogTitle>
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
