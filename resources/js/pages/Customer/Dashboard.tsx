import MainLayout from '@/layouts/MainLayout';
import { Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Wifi, Clock, DollarSign, CreditCard, Plus } from 'lucide-react';

interface Customer { id: number; username: string; name: string; balance: number; status: string; created_at: string; }
interface Recharge { id: number; namebp: string; routers: string; expiration: string; time: string; status: string; }
interface Transaction { id: number; invoice: string; plan_name: string; price: string; recharged_on: string; method: string; type: string; }

interface Props { customer: Customer; activeRecharge: Recharge | null; recentTransactions: Transaction[]; totalSpent: number; }

export default function CustomerDashboard({ customer, activeRecharge, recentTransactions, totalSpent }: Props) {
    const daysUntilExpiry = activeRecharge?.expiration
        ? Math.max(0, Math.ceil((new Date(activeRecharge.expiration).getTime() - Date.now()) / (1000 * 60 * 60 * 24)))
        : 0;

    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h2 className="text-2xl font-bold">Selamat Datang, {customer.name || customer.username}</h2>
                    <Link href={route('customer.plans.index')}><Button><Plus className="mr-2 h-4 w-4" />Lihat Paket</Button></Link>
                </div>
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Saldo Akun</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">Rp {customer.balance?.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">Saldo tersedia</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Paket Aktif</CardTitle>
                            <Wifi className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{activeRecharge ? activeRecharge.namebp : 'Tidak ada'}</div>
                            <p className="text-xs text-muted-foreground">{activeRecharge ? activeRecharge.routers : 'Belum berlangganan'}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Sisa Hari</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{daysUntilExpiry}</div>
                            <p className="text-xs text-muted-foreground">{activeRecharge ? `Kadaluarsa: ${activeRecharge.expiration}` : 'Tidak ada paket aktif'}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Pengeluaran</CardTitle>
                            <CreditCard className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">Rp {totalSpent?.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">Pengeluaran seumur hidup</p>
                        </CardContent>
                    </Card>
                </div>

                {activeRecharge && (
                    <Card>
                        <CardHeader><CardTitle>Langganan Saat Ini</CardTitle></CardHeader>
                        <CardContent>
                            <div className="grid gap-4 sm:grid-cols-3">
                                <div><p className="text-sm text-muted-foreground">Paket</p><p className="font-medium">{activeRecharge.namebp}</p></div>
                                <div><p className="text-sm text-muted-foreground">Router</p><p className="font-medium">{activeRecharge.routers}</p></div>
                                <div><p className="text-sm text-muted-foreground">Kadaluarsa</p><p className="font-medium">{activeRecharge.expiration} {activeRecharge.time}</p></div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                <Card>
                    <CardHeader><CardTitle>Transaksi Terbaru</CardTitle></CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader><TableRow><TableHead>Faktur</TableHead><TableHead>Paket</TableHead><TableHead>Jumlah</TableHead><TableHead>Metode</TableHead><TableHead>Tanggal</TableHead></TableRow></TableHeader>
                            <TableBody>
                                {recentTransactions.map((t) => (
                                    <TableRow key={t.id}>
                                        <TableCell className="font-mono text-xs">{t.invoice}</TableCell>
                                        <TableCell>{t.plan_name}</TableCell>
                                        <TableCell>Rp {parseFloat(t.price as any)?.toLocaleString()}</TableCell>
                                        <TableCell>{t.method}</TableCell>
                                        <TableCell className="text-xs">{t.recharged_on}</TableCell>
                                    </TableRow>
                                ))}
                                {recentTransactions.length === 0 && <TableRow><TableCell colSpan={5} className="text-center py-4 text-muted-foreground">Belum ada transaksi.</TableCell></TableRow>}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
