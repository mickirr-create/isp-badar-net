import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Database } from 'lucide-react';

interface TableInfo {
    name: string;
    engine: string;
    rows: number;
    data_size: number;
    index_size: number;
    auto_increment: number | null;
    collation: string;
}

interface DbStatusProps {
    tables: TableInfo[];
    database: string;
}

function formatBytes(bytes: number): string {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

export default function DbStatus({ tables, database }: DbStatusProps) {
    const totalRows = tables.reduce((sum, t) => sum + t.rows, 0);
    const totalSize = tables.reduce((sum, t) => sum + t.data_size + t.index_size, 0);

    return (
        <>
            <Head title="Status Database" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Database className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Status Database</h1>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm text-muted-foreground">Database</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-2xl font-bold">{database}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm text-muted-foreground">Total Baris</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-2xl font-bold">{totalRows.toLocaleString()}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm text-muted-foreground">Total Ukuran</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-2xl font-bold">{formatBytes(totalSize)}</p>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Daftar Tabel</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nama Tabel</TableHead>
                                    <TableHead>Engine</TableHead>
                                    <TableHead className="text-right">Baris</TableHead>
                                    <TableHead className="text-right">Data</TableHead>
                                    <TableHead className="text-right">Index</TableHead>
                                    <TableHead>Collation</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {tables.map((table) => (
                                    <TableRow key={table.name}>
                                        <TableCell className="font-mono text-sm">{table.name}</TableCell>
                                        <TableCell>{table.engine}</TableCell>
                                        <TableCell className="text-right">{table.rows.toLocaleString()}</TableCell>
                                        <TableCell className="text-right">{formatBytes(table.data_size)}</TableCell>
                                        <TableCell className="text-right">{formatBytes(table.index_size)}</TableCell>
                                        <TableCell className="text-sm">{table.collation}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
