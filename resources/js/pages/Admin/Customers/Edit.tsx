import MainLayout from '@/layouts/MainLayout';
import { Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';

interface Customer { id: number; username: string; name: string; email: string; phone: string; address: string; status: string; balance: number; }
interface Props { customer: Customer; }

export default function CustomerEdit({ customer }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: customer.name || '', email: customer.email || '', phone: customer.phone || '', address: customer.address || '', status: customer.status || 'Active', balance: customer.balance || 0,
    });
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('admin.customers.update', customer.id), { onSuccess: () => toast.success('Pelanggan berhasil diperbarui') });
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between"><h2 className="text-2xl font-bold">Edit Pelanggan: {customer.username}</h2><Link href={route('admin.customers.index')}><Button variant="outline">Kembali</Button></Link></div>
                <Card>
                    <CardHeader><CardTitle>Informasi Pelanggan</CardTitle></CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2"><Label>Username</Label><Input value={customer.username} disabled /></div>
                                <div className="space-y-2"><Label htmlFor="name">Nama Lengkap *</Label><Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} /></div>
                                <div className="space-y-2"><Label htmlFor="email">Email</Label><Input id="email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} /></div>
                                <div className="space-y-2"><Label htmlFor="phone">Telepon</Label><Input id="phone" value={data.phone} onChange={(e) => setData('phone', e.target.value)} /></div>
                                <div className="space-y-2"><Label htmlFor="status">Status</Label><Select value={data.status} onValueChange={(v) => setData('status', v)}><SelectTrigger><SelectValue /></SelectTrigger><SelectContent><SelectItem value="Active">Aktif</SelectItem><SelectItem value="Disabled">Nonaktif</SelectItem><SelectItem value="Suspended">Suspend</SelectItem></SelectContent></Select></div>
                                <div className="space-y-2"><Label htmlFor="balance">Saldo</Label><Input id="balance" type="number" value={data.balance} onChange={(e) => setData('balance', parseFloat(e.target.value) || 0)} /></div>
                            </div>
                            <div className="space-y-2"><Label htmlFor="address">Alamat</Label><Textarea id="address" value={data.address} onChange={(e) => setData('address', e.target.value)} /></div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>{processing ? 'Menyimpan...' : 'Simpan Perubahan'}</Button>
                                <Link href={route('admin.customers.index')}><Button variant="outline" type="button">Batal</Button></Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
