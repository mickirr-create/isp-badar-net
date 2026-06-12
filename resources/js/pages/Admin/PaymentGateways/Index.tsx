import MainLayout from '@/layouts/MainLayout';
import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Switch } from '@/components/ui/switch';
import { toast } from 'sonner';
import { useState } from 'react';

interface GatewaySettings {
    [key: string]: string;
}

interface Gateway {
    name: string;
    enabled: boolean;
    settings: GatewaySettings;
}

interface Props {
    gateways: Record<string, Gateway>;
}

export default function PaymentGatewaysIndex({ gateways }: Props) {
    return (
        <MainLayout>
            <div className="space-y-6">
                <h2 className="text-2xl font-bold">Payment Gateways</h2>
                <div className="grid gap-6">
                    {Object.values(gateways).map((gateway) => (
                        <GatewayCard key={gateway.name} gateway={gateway} />
                    ))}
                </div>
            </div>
        </MainLayout>
    );
}

function GatewayCard({ gateway }: { gateway: Gateway }) {
    const { data, setData, put, processing } = useForm({
        enabled: gateway.enabled,
        settings: gateway.settings,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('admin.payment-gateways.update', gateway.name), {
            onSuccess: () => toast.success(`${gateway.name} gateway updated`),
        });
    };

    return (
        <Card>
            <CardHeader>
                <div className="flex items-center justify-between">
                    <CardTitle>{gateway.name}</CardTitle>
                    <Badge variant={gateway.enabled ? 'default' : 'secondary'}>
                        {gateway.enabled ? 'Enabled' : 'Disabled'}
                    </Badge>
                </div>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="flex items-center gap-2">
                        <Switch
                            checked={data.enabled}
                            onCheckedChange={(checked) => setData('enabled', checked)}
                        />
                        <Label>Enable {gateway.name}</Label>
                    </div>
                    {Object.keys(gateway.settings).length > 0 && (
                        <div className="grid gap-4 sm:grid-cols-2">
                            {Object.entries(gateway.settings).map(([key, value]) => (
                                <div key={key} className="space-y-2">
                                    <Label className="text-sm">
                                        {key.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase())}
                                    </Label>
                                    <Input
                                        type={key.includes('key') || key.includes('secret') ? 'password' : 'text'}
                                        value={data.settings[key] || ''}
                                        onChange={(e) => {
                                            const newSettings = { ...data.settings, [key]: e.target.value };
                                            setData('settings', newSettings);
                                        }}
                                    />
                                </div>
                            ))}
                        </div>
                    )}
                    <Button type="submit" disabled={processing}>
                        {processing ? 'Saving...' : 'Save Settings'}
                    </Button>
                </form>
            </CardContent>
        </Card>
    );
}
