import { Head, useForm, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'sonner';
import { Send } from 'lucide-react';
import { useState } from 'react';

interface Customer {
    id: number;
    username: string;
    name: string;
    phone: string | null;
}

interface SendProps {
    customers: Customer[];
}

export default function Send({ customers }: SendProps) {
    const [search, setSearch] = useState('');
    const { data, setData, post, processing } = useForm({
        customer_id: '',
        message: '',
        channel: 'whatsapp',
    });

    const filtered = customers.filter((c) =>
        c.username.toLowerCase().includes(search.toLowerCase()) ||
        c.name.toLowerCase().includes(search.toLowerCase())
    );

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('admin.messages.send'), {
            onSuccess: () => {
                toast.success('Pesan berhasil dikirim');
                setData({ customer_id: '', message: '', channel: 'whatsapp' });
                setSearch('');
            },
        });
    };

    return (
        <>
            <Head title="Kirim Pesan" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Send className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Kirim Pesan</h1>
                </div>

                <form onSubmit={handleSubmit}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Kirim Pesan ke Pelanggan</CardTitle>
                            <CardDescription>Pilih pelanggan dan tulis pesan</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-2">
                                <Label>Cari Pelanggan</Label>
                                <Input
                                    placeholder="Ketik username atau nama..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                />
                                {search && (
                                    <div className="max-h-40 overflow-y-auto border rounded-md">
                                        {filtered.slice(0, 10).map((c) => (
                                            <div
                                                key={c.id}
                                                className={`p-2 cursor-pointer hover:bg-muted ${data.customer_id == String(c.id) ? 'bg-primary/10' : ''}`}
                                                onClick={() => {
                                                    setData('customer_id', String(c.id));
                                                    setSearch(`${c.username} - ${c.name}`);
                                                }}
                                            >
                                                <span className="font-medium">{c.username}</span>
                                                <span className="text-sm text-muted-foreground ml-2">{c.name}</span>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="channel">Channel</Label>
                                <select
                                    id="channel"
                                    value={data.channel}
                                    onChange={(e) => setData('channel', e.target.value)}
                                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                >
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="sms">SMS</option>
                                    <option value="email">Email</option>
                                    <option value="inbox">Inbox</option>
                                </select>
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="message">Pesan</Label>
                                <textarea
                                    id="message"
                                    value={data.message}
                                    onChange={(e) => setData('message', e.target.value)}
                                    rows={4}
                                    className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    placeholder="Tulis pesan di sini..."
                                />
                                <p className="text-xs text-muted-foreground">
                                    Placeholder: {'[[name]]'}, {'[[user_name]]'}, {'[[phone]]'}, {'[[company_name]]'}
                                </p>
                            </div>
                            <Button type="submit" disabled={processing || !data.customer_id}>
                                {processing ? 'Mengirim...' : 'Kirim Pesan'}
                            </Button>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </>
    );
}
