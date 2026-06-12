import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Plus, Pencil, Trash2, Users } from 'lucide-react';

interface User {
    id: number;
    username: string;
    fullname: string;
    email: string | null;
    phone: string | null;
    user_type: string;
    status: string;
    created_at: string | null;
}

interface UsersProps {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { search?: string };
}

export default function Users({ users, filters }: UsersProps) {
    const handleSearch = (search: string) => {
        router.get(route('admin.settings.users'), { search }, { preserveState: true });
    };

    const handleDelete = (id: number) => {
        if (confirm('Yakin ingin menghapus user ini?')) {
            router.delete(route('admin.settings.users.destroy', id));
        }
    };

    const roleBadge = (type: string) => {
        const colors: Record<string, string> = {
            SuperAdmin: 'bg-red-100 text-red-800',
            Admin: 'bg-blue-100 text-blue-800',
            Report: 'bg-green-100 text-green-800',
            Agent: 'bg-yellow-100 text-yellow-800',
            Sales: 'bg-purple-100 text-purple-800',
        };
        return colors[type] || 'bg-gray-100 text-gray-800';
    };

    return (
        <>
            <Head title="User Admin" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Users className="h-6 w-6" />
                        <h1 className="text-2xl font-bold">User Admin</h1>
                    </div>
                    <Link href={route('admin.settings.users.create')}>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Tambah User
                        </Button>
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Daftar User</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="mb-4">
                            <Input
                                placeholder="Cari user..."
                                defaultValue={filters.search || ''}
                                onChange={(e) => handleSearch(e.target.value)}
                                className="max-w-sm"
                            />
                        </div>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Username</TableHead>
                                    <TableHead>Nama</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Role</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="w-24">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {users.data.map((user) => (
                                    <TableRow key={user.id}>
                                        <TableCell className="font-medium">{user.username}</TableCell>
                                        <TableCell>{user.fullname}</TableCell>
                                        <TableCell>{user.email || '-'}</TableCell>
                                        <TableCell>
                                            <span className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${roleBadge(user.user_type)}`}>
                                                {user.user_type}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={user.status === 'Active' ? 'default' : 'secondary'}>
                                                {user.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex gap-1">
                                                <Link href={route('admin.settings.users.edit', user.id)}>
                                                    <Button variant="ghost" size="icon">
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleDelete(user.id)}
                                                >
                                                    <Trash2 className="h-4 w-4 text-destructive" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                        {users.data.length === 0 && (
                            <p className="text-center text-muted-foreground py-8">Tidak ada data user</p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
