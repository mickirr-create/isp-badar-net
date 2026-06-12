import MainLayout from '@/layouts/MainLayout';
import { Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';

interface Pool { id: number; pool_name: string; routers: string; ip_address: string; description: string; }
interface Router { id: number; name: string; }
interface Props { pool: Pool; routers: Router[]; }

export default function PoolEdit({ pool, routers }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        pool_name: pool.pool_name || '', routers: pool.routers || '', ip_address: pool.ip_address || '', description: pool.description || '',
    });
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('admin.pools.update', pool.id), { onSuccess: () => toast.success('IP Pool updated') });
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between"><h2 className="text-2xl font-bold">Edit IP Pool: {pool.pool_name}</h2><Link href={route('admin.pools.index')}><Button variant="outline">Back</Button></Link></div>
                <Card>
                    <CardHeader><CardTitle>Pool Information</CardTitle></CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2"><Label>Pool Name *</Label><Input value={data.pool_name} onChange={(e) => setData('pool_name', e.target.value)} /></div>
                                <div className="space-y-2">
                                    <Label>Router *</Label>
                                    <Select value={data.routers} onValueChange={(v) => setData('routers', v)}>
                                        <SelectTrigger><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            {routers.map((r) => <SelectItem key={r.id} value={r.name}>{r.name}</SelectItem>)}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2"><Label>IP Address *</Label><Input value={data.ip_address} onChange={(e) => setData('ip_address', e.target.value)} /></div>
                            </div>
                            <div className="space-y-2"><Label>Description</Label><Textarea value={data.description} onChange={(e) => setData('description', e.target.value)} /></div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>{processing ? 'Saving...' : 'Save Changes'}</Button>
                                <Link href={route('admin.pools.index')}><Button variant="outline" type="button">Cancel</Button></Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
