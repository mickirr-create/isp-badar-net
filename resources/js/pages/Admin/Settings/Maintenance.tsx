import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'sonner';
import { AlertTriangle } from 'lucide-react';

interface MaintenanceProps {
    settings: Record<string, string>;
}

export default function Maintenance({ settings }: MaintenanceProps) {
    const isEnabled = settings.maintenance_mode === '1';

    const { post, processing } = useForm();

    const toggleMaintenance = () => {
        post(route('admin.settings.maintenance.toggle'), {
            enabled: !isEnabled,
            onSuccess: () => {
                toast.success(isEnabled ? 'Mode maintenance dinonaktifkan' : 'Mode maintenance diaktifkan');
            },
        });
    };

    return (
        <>
            <Head title="Mode Maintenance" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <AlertTriangle className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Mode Maintenance</h1>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Status Maintenance</CardTitle>
                        <CardDescription>
                            Aktifkan mode maintenance untuk menampilkan halaman pemeliharaan kepada pengguna
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className={`rounded-lg p-4 ${isEnabled ? 'bg-yellow-50 border border-yellow-200' : 'bg-green-50 border border-green-200'}`}>
                            <div className="flex items-center gap-3">
                                <div className={`h-3 w-3 rounded-full ${isEnabled ? 'bg-yellow-500' : 'bg-green-500'}`} />
                                <span className="font-medium">
                                    {isEnabled ? 'Mode Maintenance Aktif' : 'Mode Maintenance Nonaktif'}
                                </span>
                            </div>
                            <p className="mt-2 text-sm text-muted-foreground">
                                {isEnabled
                                    ? 'Pengguna akan melihat halaman pemeliharaan saat mengakses aplikasi.'
                                    : 'Aplikasi berjalan normal untuk semua pengguna.'}
                            </p>
                        </div>
                        <Button
                            variant={isEnabled ? 'outline' : 'destructive'}
                            onClick={toggleMaintenance}
                            disabled={processing}
                        >
                            {processing ? 'Memproses...' : isEnabled ? 'Nonaktifkan Maintenance' : 'Aktifkan Maintenance'}
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
