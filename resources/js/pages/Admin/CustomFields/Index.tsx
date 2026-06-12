import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'sonner';
import { Plus, Trash2, List } from 'lucide-react';

interface Field {
    name: string;
    type: string;
    placeholder: string;
    options: string;
    required: boolean;
    show_on_register: boolean;
}

interface CustomFieldsProps {
    fields: Field[];
}

export default function CustomFields({ fields }: CustomFieldsProps) {
    const { data, setData, post, processing } = useForm({
        fields: fields.length > 0 ? fields : [
            { name: '', type: 'text', placeholder: '', options: '', required: false, show_on_register: false },
        ],
    });

    const addField = () => {
        setData('fields', [
            ...data.fields,
            { name: '', type: 'text', placeholder: '', options: '', required: false, show_on_register: false },
        ]);
    };

    const removeField = (index: number) => {
        setData('fields', data.fields.filter((_, i) => i !== index));
    };

    const updateField = (index: number, key: string, value: any) => {
        const updated = [...data.fields];
        updated[index] = { ...updated[index], [key]: value };
        setData('fields', updated);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('admin.custom-fields.save'), {
            onSuccess: () => toast.success('Field kustom berhasil disimpan'),
        });
    };

    return (
        <>
            <Head title="Field Kustom" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <List className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Field Kustom Pelanggan</h1>
                </div>

                <form onSubmit={handleSubmit}>
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle>Daftar Field</CardTitle>
                                <Button type="button" variant="outline" size="sm" onClick={addField}>
                                    <Plus className="mr-2 h-4 w-4" />
                                    Tambah Field
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {data.fields.map((field, index) => (
                                <div key={index} className="rounded-lg border p-4 space-y-3">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">Field #{index + 1}</span>
                                        <Button type="button" variant="ghost" size="icon" onClick={() => removeField(index)}>
                                            <Trash2 className="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                    <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <div className="grid gap-1">
                                            <Label className="text-xs">Nama Field</Label>
                                            <Input
                                                value={field.name}
                                                onChange={(e) => updateField(index, 'name', e.target.value)}
                                                placeholder="nama_field"
                                            />
                                        </div>
                                        <div className="grid gap-1">
                                            <Label className="text-xs">Tipe</Label>
                                            <select
                                                value={field.type}
                                                onChange={(e) => updateField(index, 'type', e.target.value)}
                                                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                            >
                                                <option value="text">Text</option>
                                                <option value="number">Number</option>
                                                <option value="select">Select</option>
                                                <option value="textarea">Textarea</option>
                                                <option value="checkbox">Checkbox</option>
                                            </select>
                                        </div>
                                        <div className="grid gap-1">
                                            <Label className="text-xs">Placeholder</Label>
                                            <Input
                                                value={field.placeholder}
                                                onChange={(e) => updateField(index, 'placeholder', e.target.value)}
                                            />
                                        </div>
                                        <div className="grid gap-1">
                                            <Label className="text-xs">Opsi (untuk select)</Label>
                                            <Input
                                                value={field.options}
                                                onChange={(e) => updateField(index, 'options', e.target.value)}
                                                placeholder="opt1,opt2,opt3"
                                            />
                                        </div>
                                    </div>
                                    <div className="flex gap-4">
                                        <label className="flex items-center gap-2 text-sm">
                                            <input
                                                type="checkbox"
                                                checked={field.required}
                                                onChange={(e) => updateField(index, 'required', e.target.checked)}
                                                className="rounded border-gray-300"
                                            />
                                            Wajib diisi
                                        </label>
                                        <label className="flex items-center gap-2 text-sm">
                                            <input
                                                type="checkbox"
                                                checked={field.show_on_register}
                                                onChange={(e) => updateField(index, 'show_on_register', e.target.checked)}
                                                className="rounded border-gray-300"
                                            />
                                            Tampilkan di registrasi
                                        </label>
                                    </div>
                                </div>
                            ))}
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Menyimpan...' : 'Simpan Semua Field'}
                            </Button>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </>
    );
}
