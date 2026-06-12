import MainLayout from '@/layouts/MainLayout';
import { Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { toast } from 'sonner';

export default function CustomerCreate() {
    const { data, setData, post, processing, errors } = useForm({ username: '', name: '', email: '', phone: '', address: '', password: '' });
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('admin.customers.store'), { onSuccess: () => toast.success('Pelanggan berhasil dibuat') });
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between"><h2 className="text-2xl font-bold">Tambah Pelanggan</h2><Link href={route('admin.customers.index')}><Button variant="outline">Kembali</Button></Link></div>
                <Card>
                    <CardHeader><CardTitle>Informasi Pelanggan</CardTitle></CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2"><Label htmlFor="username">Username *</Label><Input id="username" value={data.username} onChange={(e) => setData('username', e.target.value)} />{errors.username && <p className="text-sm text-destructive">{errors.username}</p>}</div>
                                <div className="space-y-2"><Label htmlFor="name">Nama Lengkap *</Label><Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} />{errors.name && <p className="text-sm text-destructive">{errors.name}</p>}</div>
                                <div className="space-y-2"><Label htmlFor="email">Email</Label><Input id="email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} /></div>
                                <div className="space-y-2"><Label htmlFor="phone">Telepon</Label><Input id="phone" value={data.phone} onChange={(e) => setData('phone', e.target.value)} /></div>
                            </div>
                            <div className="space-y-2"><Label htmlFor="address">Alamat</Label><Textarea id="address" value={data.address} onChange={(e) => setData('address', e.target.value)} /></div>
                            <div className="space-y-2"><Label htmlFor="password">Kata Sandi *</Label><Input id="password" type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} />{errors.password && <p className="text-sm text-destructive">{errors.password}</p>}</div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>{processing ? 'Membuat...' : 'Buat Pelanggan'}</Button>
                                <Link href={route('admin.customers.index')}><Button variant="outline" type="button">Batal</Button></Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
