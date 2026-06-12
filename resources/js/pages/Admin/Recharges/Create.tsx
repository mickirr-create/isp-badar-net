import MainLayout from '@/layouts/MainLayout';
import { Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';

interface Customer { id: number; username: string; name: string; }
interface Plan { id: number; name_plan: string; price: number; type: string; }
interface Router { id: number; name: string; }

interface Props { customers: Customer[]; plans: Plan[]; routers: Router[]; }

export default function RechargeCreate({ customers, plans, routers }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        customer_id: '', plan_id: '', router_name: '',
    });
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('admin.recharges.store'), { onSuccess: () => toast.success('Recharge successful') });
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h2 className="text-2xl font-bold">Manual Recharge</h2>
                    <Link href={route('admin.recharges.index')}><Button variant="outline">Back</Button></Link>
                </div>
                <Card>
                    <CardHeader><CardTitle>Recharge Information</CardTitle></CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="space-y-2">
                                <Label>Customer *</Label>
                                <Select value={data.customer_id} onValueChange={(v) => setData('customer_id', v)}>
                                    <SelectTrigger><SelectValue placeholder="Select customer" /></SelectTrigger>
                                    <SelectContent>
                                        {customers.map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.username} - {c.name}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                                {errors.customer_id && <p className="text-sm text-destructive">{errors.customer_id}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label>Plan *</Label>
                                <Select value={data.plan_id} onValueChange={(v) => setData('plan_id', v)}>
                                    <SelectTrigger><SelectValue placeholder="Select plan" /></SelectTrigger>
                                    <SelectContent>
                                        {plans.map((p) => <SelectItem key={p.id} value={String(p.id)}>{p.name_plan} - Rp {p.price?.toLocaleString()} ({p.type})</SelectItem>)}
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
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>{processing ? 'Processing...' : 'Recharge'}</Button>
                                <Link href={route('admin.recharges.index')}><Button variant="outline" type="button">Cancel</Button></Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
