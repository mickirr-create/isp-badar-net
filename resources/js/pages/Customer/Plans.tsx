import MainLayout from '@/layouts/MainLayout';
import { router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Wifi, Clock, DollarSign } from 'lucide-react';

interface Plan { id: number; name_plan: string; price: number; validity: number; validity_unit: string; type: string; description: string; bandwidth?: { name_bw: string; rate_down: number; rate_up: number; rate_down_unit: string }; }
interface Props { plans: Plan[]; filters: { type?: string }; }

export default function CustomerPlans({ plans, filters }: Props) {
    const handleFilter = (type: string) => {
        router.get(route('customer.plans.index'), { type }, { preserveState: true });
    };

    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h2 className="text-2xl font-bold">Paket Tersedia</h2>
                    <Select value={filters.type || 'all'} onValueChange={(v) => handleFilter(v === 'all' ? '' : v)}>
                        <SelectTrigger className="w-[180px]"><SelectValue placeholder="Semua Jenis" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Jenis</SelectItem>
                            <SelectItem value="Hotspot">Hotspot</SelectItem>
                            <SelectItem value="PPPoE">PPPoE</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {plans.map((plan) => (
                        <Card key={plan.id} className="hover:shadow-md transition-shadow">
                            <CardHeader><div className="flex items-center justify-between"><CardTitle className="text-lg">{plan.name_plan}</CardTitle><Badge variant="outline">{plan.type}</Badge></div></CardHeader>
                            <CardContent className="space-y-4">
                                {plan.bandwidth && <div className="flex items-center gap-2 text-sm text-muted-foreground"><Wifi className="h-4 w-4" /><span>{plan.bandwidth.name_bw} ({plan.bandwidth.rate_down} {plan.bandwidth.rate_down_unit})</span></div>}
                                <div className="flex items-center gap-2 text-sm text-muted-foreground"><Clock className="h-4 w-4" /><span>{plan.validity} {plan.validity_unit}</span></div>
                                <div className="flex items-center gap-2 text-lg font-bold text-primary"><DollarSign className="h-5 w-5" /><span>Rp {plan.price?.toLocaleString()}</span></div>
                                {plan.description && <p className="text-sm text-muted-foreground">{plan.description}</p>}
                            </CardContent>
                        </Card>
                    ))}
                    {plans.length === 0 && <div className="col-span-full text-center py-12 text-muted-foreground">Belum ada paket tersedia.</div>}
                </div>
            </div>
        </MainLayout>
    );
}
