import MainLayout from '@/layouts/MainLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Users, Wifi, Router, DollarSign, TrendingUp, Activity } from 'lucide-react';

interface Stats {
    totalCustomers: number;
    activeCustomers: number;
    totalPlans: number;
    totalRouters: number;
    onlineRouters: number;
    todayRevenue: number;
    monthlyRevenue: number;
    dailyTransactions: number;
}

interface Transaction { id: number; invoice: string; username: string; plan_name: string; price: string; method: string; recharged_on: string; }
interface Customer { id: number; username: string; name: string; status: string; created_at: string; }

interface Props { stats: Stats; recentTransactions: Transaction[]; recentCustomers: Customer[]; }

export default function Dashboard({ stats, recentTransactions, recentCustomers }: Props) {
    return (
        <MainLayout>
            <div className="space-y-6">
                <h2 className="text-2xl font-bold">Dasbor Admin</h2>
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Pelanggan</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalCustomers}</div>
                            <p className="text-xs text-muted-foreground">{stats.activeCustomers} berlangganan aktif</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Paket Aktif</CardTitle>
                            <Wifi className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalPlans}</div>
                            <p className="text-xs text-muted-foreground">Jenis paket tersedia</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Router</CardTitle>
                            <Router className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalRouters}</div>
                            <p className="text-xs text-muted-foreground">{stats.onlineRouters} online</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pendapatan Hari Ini</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">Rp {stats.todayRevenue?.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">{stats.dailyTransactions} transaksi</p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader><CardTitle className="flex items-center gap-2"><TrendingUp className="h-4 w-4" />Transaksi Terbaru</CardTitle></CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader><TableRow><TableHead>Faktur</TableHead><TableHead>Pelanggan</TableHead><TableHead>Paket</TableHead><TableHead>Jumlah</TableHead><TableHead>Tanggal</TableHead></TableRow></TableHeader>
                                <TableBody>
                                    {recentTransactions.map((t) => (
                                        <TableRow key={t.id}>
                                            <TableCell className="font-mono text-xs">{t.invoice}</TableCell>
                                            <TableCell>{t.username}</TableCell>
                                            <TableCell>{t.plan_name}</TableCell>
                                            <TableCell>Rp {parseFloat(t.price as any)?.toLocaleString()}</TableCell>
                                            <TableCell className="text-xs">{t.recharged_on}</TableCell>
                                        </TableRow>
                                    ))}
                                    {recentTransactions.length === 0 && <TableRow><TableCell colSpan={5} className="text-center py-4 text-muted-foreground">Belum ada transaksi.</TableCell></TableRow>}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle className="flex items-center gap-2"><Activity className="h-4 w-4" />Pelanggan Terbaru</CardTitle></CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader><TableRow><TableHead>Username</TableHead><TableHead>Nama</TableHead><TableHead>Status</TableHead><TableHead>Bergabung</TableHead></TableRow></TableHeader>
                                <TableBody>
                                    {recentCustomers.map((c) => (
                                        <TableRow key={c.id}>
                                            <TableCell className="font-medium">{c.username}</TableCell>
                                            <TableCell>{c.name}</TableCell>
                                            <TableCell><Badge variant={c.status === 'Active' ? 'default' : 'secondary'}>{c.status === 'Active' ? 'Aktif' : 'Nonaktif'}</Badge></TableCell>
                                            <TableCell className="text-xs">{c.created_at}</TableCell>
                                        </TableRow>
                                    ))}
                                    {recentCustomers.length === 0 && <TableRow><TableCell colSpan={4} className="text-center py-4 text-muted-foreground">Belum ada pelanggan.</TableCell></TableRow>}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader><CardTitle>Pendapatan Bulanan</CardTitle></CardHeader>
                    <CardContent>
                        <div className="text-center py-4">
                            <div className="text-4xl font-bold text-primary">Rp {stats.monthlyRevenue?.toLocaleString()}</div>
                            <p className="text-muted-foreground mt-2">Total pendapatan bulan ini</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
