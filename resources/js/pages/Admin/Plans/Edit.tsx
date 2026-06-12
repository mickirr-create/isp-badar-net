import MainLayout from '@/layouts/MainLayout';
import { Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';

interface Bandwidth { id: number; name_bw: string; }
interface Plan { id: number; name_plan: string; id_bw: number; price: number; description: string; validity: number; validity_unit: string; expired_date: number; enabled: boolean; is_radius: boolean; pool: string; type: string; device: string; }
interface Props { plan: Plan; bandwidths: Bandwidth[]; }

export default function PlanEdit({ plan, bandwidths }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name_plan: plan.name_plan || '', id_bw: String(plan.id_bw) || '', price: plan.price || 0, description: plan.description || '', validity: plan.validity || 1, validity_unit: plan.validity_unit || 'Months', expired_date: plan.expired_date || 20, enabled: plan.enabled ?? true, is_radius: plan.is_radius ?? false, pool: plan.pool || '', type: plan.type || 'Hotspot', device: plan.device || 'MikrotikHotspot',
    });
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('admin.plans.update', plan.id), { onSuccess: () => toast.success('Paket berhasil diperbarui') });
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between"><h2 className="text-2xl font-bold">Edit Paket: {plan.name_plan}</h2><Link href={route('admin.plans.index')}><Button variant="outline">Kembali</Button></Link></div>
                <Card><CardHeader><CardTitle>Informasi Paket</CardTitle></CardHeader><CardContent>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="space-y-2"><Label>Nama Paket *</Label><Input value={data.name_plan} onChange={(e) => setData('name_plan', e.target.value)} /></div>
                            <div className="space-y-2"><Label>Jenis *</Label><Select value={data.type} onValueChange={(v) => { setData('type', v); setData('device', v === 'Hotspot' ? 'MikrotikHotspot' : v === 'PPPoE' ? 'MikrotikPppoe' : 'Dummy'); }}><SelectTrigger><SelectValue /></SelectTrigger><SelectContent><SelectItem value="Hotspot">Hotspot</SelectItem><SelectItem value="PPPoE">PPPoE</SelectItem><SelectItem value="Balance">Saldo</SelectItem></SelectContent></Select></div>
                            <div className="space-y-2"><Label>Bandwidth *</Label><Select value={data.id_bw} onValueChange={(v) => setData('id_bw', v)}><SelectTrigger><SelectValue /></SelectTrigger><SelectContent>{bandwidths.map((bw) => <SelectItem key={bw.id} value={String(bw.id)}>{bw.name_bw}</SelectItem>)}</SelectContent></Select></div>
                            <div className="space-y-2"><Label>Harga *</Label><Input type="number" value={data.price} onChange={(e) => setData('price', parseFloat(e.target.value) || 0)} /></div>
                            <div className="space-y-2"><Label>Masa Aktif *</Label><Input type="number" value={data.validity} onChange={(e) => setData('validity', parseInt(e.target.value) || 1)} /></div>
                            <div className="space-y-2"><Label>Satuan *</Label><Select value={data.validity_unit} onValueChange={(v) => setData('validity_unit', v)}><SelectTrigger><SelectValue /></SelectTrigger><SelectContent><SelectItem value="Months">Bulan</SelectItem><SelectItem value="Period">Periode</SelectItem><SelectItem value="Days">Hari</SelectItem><SelectItem value="Hrs">Jam</SelectItem><SelectItem value="Mins">Menit</SelectItem></SelectContent></Select></div>
                        </div>
                        <div className="space-y-2"><Label>Deskripsi</Label><Textarea value={data.description} onChange={(e) => setData('description', e.target.value)} /></div>
                        <div className="flex gap-2"><Button type="submit" disabled={processing}>{processing ? 'Menyimpan...' : 'Simpan Perubahan'}</Button><Link href={route('admin.plans.index')}><Button variant="outline" type="button">Batal</Button></Link></div>
                    </form>
                </CardContent></Card>
            </div>
        </MainLayout>
    );
}
