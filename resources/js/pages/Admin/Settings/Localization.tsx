import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'sonner';
import { Globe } from 'lucide-react';

interface LocalizationProps {
    settings: Record<string, string>;
}

export default function Localization({ settings }: LocalizationProps) {
    const { data, setData, put, processing } = useForm({
        timezone: settings.timezone || 'Asia/Jakarta',
        date_format: settings.date_format || 'd/m/Y',
        default_language: settings.default_language || 'id',
        country_code: settings.country_code || '62',
    });

    const timezones = [
        'Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura',
        'Asia/Singapore', 'Asia/Kuala_Lumpur',
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('admin.settings.update'), {
            onSuccess: () => toast.success('Pengaturan lokal berhasil disimpan'),
        });
    };

    return (
        <>
            <Head title="Lokalisasi" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Globe className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Lokalisasi</h1>
                </div>

                <form onSubmit={handleSubmit}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Pengaturan Wilayah</CardTitle>
                            <CardDescription>Format tanggal, timezone, dan bahasa</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-2">
                                <Label htmlFor="timezone">Timezone</Label>
                                <select
                                    id="timezone"
                                    value={data.timezone}
                                    onChange={(e) => setData('timezone', e.target.value)}
                                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                >
                                    {timezones.map((tz) => (
                                        <option key={tz} value={tz}>{tz}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="date_format">Format Tanggal</Label>
                                    <Input
                                        id="date_format"
                                        value={data.date_format}
                                        onChange={(e) => setData('date_format', e.target.value)}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="default_language">Bahasa</Label>
                                    <select
                                        id="default_language"
                                        value={data.default_language}
                                        onChange={(e) => setData('default_language', e.target.value)}
                                        className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    >
                                        <option value="id">Indonesia</option>
                                        <option value="en">English</option>
                                    </select>
                                </div>
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="country_code">Kode Negara</Label>
                                <Input
                                    id="country_code"
                                    value={data.country_code}
                                    onChange={(e) => setData('country_code', e.target.value)}
                                    className="w-24"
                                />
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
