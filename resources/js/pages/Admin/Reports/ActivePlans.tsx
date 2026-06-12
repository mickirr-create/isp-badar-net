import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Radio } from 'lucide-react';

interface ActivePlan {
    id: number;
    username: string;
    plan_name: string;
    expiration: string;
    status: string;
    customer?: { name: string; username: string };
    plan?: { name: string; price: number };
}

interface ActivePlansProps {
    activePlans: { data: ActivePlan[] };
    expiredPlans: { data: ActivePlan[] };
}

export default function ActivePlans({ activePlans, expiredPlans }: ActivePlansProps) {
    return (
        <>
            <Head title="Paket Aktif" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Radio className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Status Paket</h1>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Paket Aktif ({activePlans.data.length})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Pelanggan</TableHead>
                                    <TableHead>Paket</TableHead>
                                    <TableHead>Expired</TableHead>
                                    <TableHead>Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {activePlans.data.map((rp) => (
                                    <TableRow key={rp.id}>
                                        <TableCell>{rp.customer?.name || rp.username}</TableCell>
                                        <TableCell>{rp.plan?.name || rp.plan_name}</TableCell>
                                        <TableCell className="text-sm">{rp.expiration}</TableCell>
                                        <TableCell>
                                            <Badge className="bg-green-100 text-green-800">Aktif</Badge>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                        {activePlans.data.length === 0 && (
                            <p className="text-center text-muted-foreground py-8">Tidak ada paket aktif</p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Paket Kadaluarsa ({expiredPlans.data.length})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Pelanggan</TableHead>
                                    <TableHead>Paket</TableHead>
                                    <TableHead>Expired</TableHead>
                                    <TableHead>Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {expiredPlans.data.map((rp) => (
                                    <TableRow key={rp.id}>
                                        <TableCell>{rp.customer?.name || rp.username}</TableCell>
                                        <TableCell>{rp.plan?.name || rp.plan_name}</TableCell>
                                        <TableCell className="text-sm">{rp.expiration}</TableCell>
                                        <TableCell>
                                            <Badge variant="destructive">Kadaluarsa</Badge>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                        {expiredPlans.data.length === 0 && (
                            <p className="text-center text-muted-foreground py-8">Tidak ada paket kadaluarsa</p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
