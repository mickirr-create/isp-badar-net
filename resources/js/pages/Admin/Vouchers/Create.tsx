import MainLayout from '@/layouts/MainLayout';
import { Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';

interface Plan { id: number; name_plan: string; type: string; }
interface Router { id: number; name: string; }
interface Props { plans: Plan[]; routers: Router[]; }

export default function VoucherCreate({ plans, routers }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        plan_id: '', router_name: '', quantity: 10,
    });
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('admin.vouchers.store'), { onSuccess: () => toast.success('Vouchers generated') });
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h2 className="text-2xl font-bold">Generate Vouchers</h2>
                    <Link href={route('admin.vouchers.index')}><Button variant="outline">Back</Button></Link>
                </div>
                <Card>
                    <CardHeader><CardTitle>Voucher Settings</CardTitle></CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-3">
                                <div className="space-y-2">
                                    <Label>Plan *</Label>
                                    <Select value={data.plan_id} onValueChange={(v) => setData('plan_id', v)}>
                                        <SelectTrigger><SelectValue placeholder="Select plan" /></SelectTrigger>
                                        <SelectContent>
                                            {plans.map((p) => <SelectItem key={p.id} value={String(p.id)}>{p.name_plan} ({p.type})</SelectItem>)}
                                        </SelectContent>
                                    </Select>
                                    {errors.plan_id && <p className="text-sm text-destructive">{errors.plan_id}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label>Router *</Label>
                                    <Select value={data.router_name} onValueChange={(v) => setData('router_name', v)}>
                                        <SelectTrigger><SelectValue placeholder="Select router" /></SelectTrigger>
                                        <SelectContent>
                                            {routers.map((r) => <SelectItem key={r.id} value={r.name}>{r.name}</SelectItem>)}
                                        </SelectContent>
                                    </Select>
                                    {errors.router_name && <p className="text-sm text-destructive">{errors.router_name}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label>Quantity *</Label>
                                    <Input type="number" min="1" max="100" value={data.quantity} onChange={(e) => setData('quantity', parseInt(e.target.value) || 10)} />
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>{processing ? 'Generating...' : 'Generate Vouchers'}</Button>
                                <Link href={route('admin.vouchers.index')}><Button variant="outline" type="button">Cancel</Button></Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
