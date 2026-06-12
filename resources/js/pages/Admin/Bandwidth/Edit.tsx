import MainLayout from '@/layouts/MainLayout';
import { Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';

interface Bandwidth { id: number; name_bw: string; rate_down: number; rate_up: number; rate_down_unit: string; rate_up_unit: string; burst: string; }
interface Props { bandwidth: Bandwidth; }

export default function BandwidthEdit({ bandwidth }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name_bw: bandwidth.name_bw || '', rate_down: bandwidth.rate_down || 0, rate_up: bandwidth.rate_up || 0, rate_down_unit: bandwidth.rate_down_unit || 'Mbps', rate_up_unit: bandwidth.rate_up_unit || 'Mbps', burst: bandwidth.burst || '',
    });
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('admin.bandwidth.update', bandwidth.id), { onSuccess: () => toast.success('Bandwidth updated') });
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between"><h2 className="text-2xl font-bold">Edit Bandwidth: {bandwidth.name_bw}</h2><Link href={route('admin.bandwidth.index')}><Button variant="outline">Back</Button></Link></div>
                <Card>
                    <CardHeader><CardTitle>Bandwidth Information</CardTitle></CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2"><Label>Name *</Label><Input value={data.name_bw} onChange={(e) => setData('name_bw', e.target.value)} /></div>
                                <div className="space-y-2"><Label>Download Rate *</Label><Input type="number" value={data.rate_down} onChange={(e) => setData('rate_down', parseFloat(e.target.value) || 0)} /></div>
                                <div className="space-y-2"><Label>Upload Rate *</Label><Input type="number" value={data.rate_up} onChange={(e) => setData('rate_up', parseFloat(e.target.value) || 0)} /></div>
                                <div className="space-y-2">
                                    <Label>Download Unit</Label>
                                    <Select value={data.rate_down_unit} onValueChange={(v) => setData('rate_down_unit', v)}>
                                        <SelectTrigger><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="Mbps">Mbps</SelectItem>
                                            <SelectItem value="Kbps">Kbps</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label>Upload Unit</Label>
                                    <Select value={data.rate_up_unit} onValueChange={(v) => setData('rate_up_unit', v)}>
                                        <SelectTrigger><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="Mbps">Mbps</SelectItem>
                                            <SelectItem value="Kbps">Kbps</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2"><Label>Burst</Label><Input value={data.burst} onChange={(e) => setData('burst', e.target.value)} placeholder="e.g. 1M/2M" /></div>
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>{processing ? 'Saving...' : 'Save Changes'}</Button>
                                <Link href={route('admin.bandwidth.index')}><Button variant="outline" type="button">Cancel</Button></Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
