import { Head, useForm, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'sonner';
import { Settings } from 'lucide-react';

interface SettingsProps {
    settings: Record<string, string>;
}

export default function AppSettings({ settings }: SettingsProps) {
    const { data, setData, put, processing } = useForm({
        company_name: settings.company_name || 'Badar Net',
        company_phone: settings.company_phone || '',
        company_address: settings.company_address || '',
        tax: settings.tax || '0',
        tax_enabled: settings.tax_enabled === '1',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('admin.settings.update'), {
            onSuccess: () => toast.success('Pengaturan berhasil disimpan'),
        });
    };

    return (
        <>
            <Head title="Pengaturan Aplikasi" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Settings className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Pengaturan Aplikasi</h1>
                </div>

                <form onSubmit={handleSubmit}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Informasi Perusahaan</CardTitle>
                            <CardDescription>Pengaturan dasar aplikasi</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-2">
                                <Label htmlFor="company_name">Nama Perusahaan</Label>
                                <Input
                                    id="company_name"
                                    value={data.company_name}
                                    onChange={(e) => setData('company_name', e.target.value)}
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="company_phone">Telepon</Label>
                                <Input
                                    id="company_phone"
                                    value={data.company_phone}
                                    onChange={(e) => setData('company_phone', e.target.value)}
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="company_address">Alamat</Label>
                                <Input
                                    id="company_address"
                                    value={data.company_address}
                                    onChange={(e) => setData('company_address', e.target.value)}
                                />
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="tax">Pajak (%)</Label>
                                    <Input
                                        id="tax"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        value={data.tax}
                                        onChange={(e) => setData('tax', e.target.value)}
                                    />
                                </div>
                                <div className="flex items-end pb-1">
                                    <label className="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            checked={data.tax_enabled}
                                            onChange={(e) => setData('tax_enabled', e.target.checked)}
                                            className="rounded border-gray-300"
                                        />
                                        Aktifkan Pajak
                                    </label>
                                </div>
                            </div>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Menyimpan...' : 'Simpan'}
                            </Button>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </>
    );
}
