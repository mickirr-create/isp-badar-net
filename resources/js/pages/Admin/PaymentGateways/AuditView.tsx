import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, CreditCard } from 'lucide-react';

interface AuditViewProps {
    audit: {
        id: number;
        gateway: string;
        username: string;
        status: string;
        gateway_fee: number;
        created_at: string;
        plan?: { name: string; price: number };
        router?: { name: string };
    };
}

export default function AuditView({ audit }: AuditViewProps) {
    return (
        <>
            <Head title="Detail Audit Gateway" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Button variant="ghost" size="icon" onClick={() => history.back()}>
                        <ArrowLeft className="h-5 w-5" />
                    </Button>
                    <CreditCard className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Detail Audit #{audit.id}</h1>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Informasi Transaksi</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <p className="text-sm text-muted-foreground">ID</p>
                                <p className="font-mono">{audit.id}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Gateway</p>
                                <Badge variant="outline">{audit.gateway}</Badge>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Username</p>
                                <p>{audit.username}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Status</p>
                                <Badge className={audit.status === 'Success' ? 'bg-green-100 text-green-800' : audit.status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}>
                                    {audit.status}
                                </Badge>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Paket</p>
                                <p>{audit.plan?.name || '-'}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Harga Paket</p>
                                <p>Rp {(audit.plan?.price || 0).toLocaleString('id-ID')}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Gateway Fee</p>
                                <p>Rp {(audit.gateway_fee || 0).toLocaleString('id-ID')}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Router</p>
                                <p>{audit.router?.name || '-'}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Tanggal</p>
                                <p>{audit.created_at ? new Date(audit.created_at).toLocaleString('id-ID') : '-'}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Button variant="outline" onClick={() => history.back()}>
                    Kembali
                </Button>
            </div>
        </>
    );
}
