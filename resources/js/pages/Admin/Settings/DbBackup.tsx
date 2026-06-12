import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Download, Database } from 'lucide-react';
import { useState } from 'react';

interface DbBackupProps {
    tables: string[];
}

export default function DbBackup({ tables }: DbBackupProps) {
    const [selected, setSelected] = useState<string[]>(tables);
    const [downloading, setDownloading] = useState(false);

    const toggleTable = (table: string) => {
        setSelected((prev) =>
            prev.includes(table) ? prev.filter((t) => t !== table) : [...prev, table]
        );
    };

    const selectAll = () => setSelected(tables);
    const selectNone = () => setSelected([]);

    const handleBackup = () => {
        setDownloading(true);
        router.post(route('admin.settings.db-backup.download'), { tables: selected }, {
            onFinish: () => setDownloading(false),
        });
    };

    return (
        <>
            <Head title="Backup Database" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Database className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Backup Database</h1>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Pilih Tabel untuk Backup</CardTitle>
                        <CardDescription>
                            Pilih tabel yang ingin di-backup sebagai file JSON
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex gap-2">
                            <Button variant="outline" size="sm" onClick={selectAll}>
                                Pilih Semua
                            </Button>
                            <Button variant="outline" size="sm" onClick={selectNone}>
                                Batal Pilih
                            </Button>
                            <span className="text-sm text-muted-foreground self-center">
                                {selected.length} dari {tables.length} tabel dipilih
                            </span>
                        </div>
                        <div className="grid grid-cols-2 md:grid-cols-3 gap-2">
                            {tables.map((table) => (
                                <label
                                    key={table}
                                    className={`flex items-center gap-2 rounded-lg border p-3 cursor-pointer transition-colors ${
                                        selected.includes(table) ? 'bg-primary/10 border-primary' : 'hover:bg-muted'
                                    }`}
                                >
                                    <input
                                        type="checkbox"
                                        checked={selected.includes(table)}
                                        onChange={() => toggleTable(table)}
                                        className="rounded border-gray-300"
                                    />
                                    <span className="font-mono text-sm">{table}</span>
                                </label>
                            ))}
                        </div>
                        <Button onClick={handleBackup} disabled={selected.length === 0 || downloading}>
                            <Download className="mr-2 h-4 w-4" />
                            {downloading ? 'Mengunduh...' : 'Download Backup'}
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
