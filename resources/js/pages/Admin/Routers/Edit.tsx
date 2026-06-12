import MainLayout from '@/layouts/MainLayout';
import { Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';

interface RouterItem { id: number; name: string; ip_address: string; username: string; password: string; community: string; description: string; type: string; enabled: boolean; status: string; }
interface Props { router: RouterItem; }

export default function RouterEdit({ router: r }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: r.name || '', ip_address: r.ip_address || '', username: r.username || '', password: r.password || '', community: r.community || '', description: r.description || '', type: r.type || 'Mikrotik',
    });
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('admin.routers.update', r.id), { onSuccess: () => toast.success('Router berhasil diperbarui') });
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between"><h2 className="text-2xl font-bold">Edit Router: {r.name}</h2><Link href={route('admin.routers.index')}><Button variant="outline">Kembali</Button></Link></div>
                <Card><CardHeader><CardTitle>Informasi Router</CardTitle></CardHeader><CardContent>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="space-y-2"><Label>Nama *</Label><Input value={data.name} onChange={(e) => setData('name', e.target.value)} /></div>
                            <div className="space-y-2"><Label>IP Address *</Label><Input value={data.ip_address} onChange={(e) => setData('ip_address', e.target.value)} /></div>
                            <div className="space-y-2"><Label>Username</Label><Input value={data.username} onChange={(e) => setData('username', e.target.value)} /></div>
                            <div className="space-y-2"><Label>Password</Label><Input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} /></div>
                            <div className="space-y-2"><Label>SNMP Community</Label><Input value={data.community} onChange={(e) => setData('community', e.target.value)} /></div>
                            <div className="space-y-2"><Label>Jenis</Label><Select value={data.type} onValueChange={(v) => setData('type', v)}><SelectTrigger><SelectValue /></SelectTrigger><SelectContent><SelectItem value="Mikrotik">Mikrotik</SelectItem><SelectItem value="Radius">Radius</SelectItem></SelectContent></Select></div>
                        </div>
                        <div className="space-y-2"><Label>Deskripsi</Label><Textarea value={data.description} onChange={(e) => setData('description', e.target.value)} /></div>
                        <div className="flex gap-2"><Button type="submit" disabled={processing}>{processing ? 'Menyimpan...' : 'Simpan Perubahan'}</Button><Link href={route('admin.routers.index')}><Button variant="outline" type="button">Batal</Button></Link></div>
                    </form>
                </CardContent></Card>
            </div>
        </MainLayout>
    );
}
