import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'sonner';
import { UserPlus } from 'lucide-react';

interface UserFormProps {
    editUser?: {
        id: number;
        username: string;
        fullname: string;
        email: string | null;
        phone: string | null;
        user_type: string;
        status: string;
    };
}

export default function UserForm({ editUser }: UserFormProps) {
    const isEdit = !!editUser;

    const { data, setData, post, put, processing, errors } = useForm({
        username: editUser?.username || '',
        fullname: editUser?.fullname || '',
        email: editUser?.email || '',
        phone: editUser?.phone || '',
        password: '',
        user_type: editUser?.user_type || 'Admin',
        status: editUser?.status || 'Active',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (isEdit) {
            put(route('admin.settings.users.update', editUser!.id), {
                onSuccess: () => toast.success('User berhasil diperbarui'),
            });
        } else {
            post(route('admin.settings.users.store'), {
                onSuccess: () => toast.success('User berhasil dibuat'),
            });
        }
    };

    return (
        <>
            <Head title={isEdit ? 'Edit User' : 'Tambah User'} />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <UserPlus className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">{isEdit ? 'Edit User' : 'Tambah User Baru'}</h1>
                </div>

                <form onSubmit={handleSubmit}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Data User</CardTitle>
                            <CardDescription>
                                {isEdit ? 'Perbarui informasi user' : 'Isi data untuk user baru'}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-2">
                                <Label htmlFor="username">Username</Label>
                                <Input
                                    id="username"
                                    value={data.username}
                                    onChange={(e) => setData('username', e.target.value)}
                                    disabled={isEdit}
                                />
                                {errors.username && <p className="text-sm text-destructive">{errors.username}</p>}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="fullname">Nama Lengkap</Label>
                                <Input
                                    id="fullname"
                                    value={data.fullname}
                                    onChange={(e) => setData('fullname', e.target.value)}
                                />
                                {errors.fullname && <p className="text-sm text-destructive">{errors.fullname}</p>}
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="phone">Telepon</Label>
                                    <Input
                                        id="phone"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                    />
                                </div>
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="password">
                                    {isEdit ? 'Password (kosongkan jika tidak diubah)' : 'Password'}
                                </Label>
                                <Input
                                    id="password"
                                    type="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                />
                                {errors.password && <p className="text-sm text-destructive">{errors.password}</p>}
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="user_type">Role</Label>
                                    <select
                                        id="user_type"
                                        value={data.user_type}
                                        onChange={(e) => setData('user_type', e.target.value)}
                                        className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    >
                                        <option value="SuperAdmin">SuperAdmin</option>
                                        <option value="Admin">Admin</option>
                                        <option value="Report">Report</option>
                                        <option value="Agent">Agent</option>
                                        <option value="Sales">Sales</option>
                                    </select>
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="status">Status</Label>
                                    <select
                                        id="status"
                                        value={data.status}
                                        onChange={(e) => setData('status', e.target.value)}
                                        className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    >
                                        <option value="Active">Active</option>
                                        <option value="Disabled">Disabled</option>
                                    </select>
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Menyimpan...' : 'Simpan'}
                                </Button>
                                <Button type="button" variant="outline" onClick={() => history.back()}>
                                    Batal
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </>
    );
}
