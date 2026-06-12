import MainLayout from '@/layouts/MainLayout';
import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { toast } from 'sonner';

interface Customer { id: number; username: string; name: string; email: string; phone: string; address: string; balance: number; status: string; created_at: string; recharges?: Array<{ namebp: string; status: string; expiration: string; routers: string }>; fields?: Array<{ field_name: string; field_value: string }>; }
interface Props { customer: Customer; }

export default function CustomerProfile({ customer }: Props) {
    const { data: profileData, setData: setProfileData, put: putProfile, processing: profileProcessing } = useForm({
        name: customer.name || '', email: customer.email || '', phone: customer.phone || '', address: customer.address || '',
    });
    const { data: passwordData, setData: setPasswordData, post: postPassword, processing: passwordProcessing, reset: resetPassword } = useForm({
        current_password: '', password: '', password_confirmation: '',
    });

    const handleProfileSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        putProfile(route('customer.profile.update'), { onSuccess: () => toast.success('Profil berhasil diperbarui') });
    };
    const handlePasswordSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        postPassword(route('customer.profile.password'), { onSuccess: () => { toast.success('Kata sandi berhasil diubah'); resetPassword(); } });
    };

    const activeRecharge = customer.recharges?.find((r) => r.status === 'on');

    return (
        <MainLayout>
            <div className="space-y-6">
                <h2 className="text-2xl font-bold">Profil Saya</h2>
                <div className="grid gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader><CardTitle>Informasi Akun</CardTitle></CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex justify-between"><span className="text-muted-foreground">Username</span><span className="font-medium">{customer.username}</span></div>
                            <div className="flex justify-between"><span className="text-muted-foreground">Status</span><Badge variant={customer.status === 'Active' ? 'default' : 'destructive'}>{customer.status === 'Active' ? 'Aktif' : 'Nonaktif'}</Badge></div>
                            <div className="flex justify-between"><span className="text-muted-foreground">Saldo</span><span className="font-medium">Rp {customer.balance?.toLocaleString()}</span></div>
                            <div className="flex justify-between"><span className="text-muted-foreground">Anggota Sejak</span><span>{customer.created_at}</span></div>
                            {activeRecharge && (<><Separator /><div className="space-y-2"><h4 className="font-medium">Paket Aktif</h4>
                                <div className="flex justify-between"><span className="text-muted-foreground">Paket</span><span>{activeRecharge.namebp}</span></div>
                                <div className="flex justify-between"><span className="text-muted-foreground">Router</span><span>{activeRecharge.routers}</span></div>
                                <div className="flex justify-between"><span className="text-muted-foreground">Kadaluarsa</span><span>{activeRecharge.expiration}</span></div>
                            </div></>)}
                        </CardContent>
                    </Card>
                    <div className="space-y-6">
                        <Card>
                            <CardHeader><CardTitle>Edit Profil</CardTitle></CardHeader>
                            <CardContent>
                                <form onSubmit={handleProfileSubmit} className="space-y-4">
                                    <div className="space-y-2"><Label>Nama Lengkap</Label><Input value={profileData.name} onChange={(e) => setProfileData('name', e.target.value)} /></div>
                                    <div className="space-y-2"><Label>Email</Label><Input type="email" value={profileData.email} onChange={(e) => setProfileData('email', e.target.value)} /></div>
                                    <div className="space-y-2"><Label>Telepon</Label><Input value={profileData.phone} onChange={(e) => setProfileData('phone', e.target.value)} /></div>
                                    <div className="space-y-2"><Label>Alamat</Label><Textarea value={profileData.address} onChange={(e) => setProfileData('address', e.target.value)} /></div>
                                    <Button type="submit" disabled={profileProcessing}>{profileProcessing ? 'Menyimpan...' : 'Simpan Perubahan'}</Button>
                                </form>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader><CardTitle>Ubah Kata Sandi</CardTitle></CardHeader>
                            <CardContent>
                                <form onSubmit={handlePasswordSubmit} className="space-y-4">
                                    <div className="space-y-2"><Label>Kata Sandi Saat Ini</Label><Input type="password" value={passwordData.current_password} onChange={(e) => setPasswordData('current_password', e.target.value)} /></div>
                                    <div className="space-y-2"><Label>Kata Sandi Baru</Label><Input type="password" value={passwordData.password} onChange={(e) => setPasswordData('password', e.target.value)} /></div>
                                    <div className="space-y-2"><Label>Konfirmasi Kata Sandi Baru</Label><Input type="password" value={passwordData.password_confirmation} onChange={(e) => setPasswordData('password_confirmation', e.target.value)} /></div>
                                    <Button type="submit" disabled={passwordProcessing}>{passwordProcessing ? 'Mengubah...' : 'Ubah Kata Sandi'}</Button>
                                </form>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </MainLayout>
    );
}
