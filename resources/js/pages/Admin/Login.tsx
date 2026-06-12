import { FormEventHandler } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        username: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('admin.login'));
    };

    return (
        <>
            <Head title="Masuk Admin" />
            <div className="flex min-h-screen items-center justify-center bg-background">
                <Card className="w-full max-w-md">
                    <CardHeader className="text-center">
                        <CardTitle className="text-2xl">Badar Net</CardTitle>
                        <CardDescription>Masuk Admin</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="username">Username</Label>
                                <Input id="username" value={data.username} onChange={(e) => setData('username', e.target.value)} required autoFocus />
                                {errors.username && <p className="text-sm text-destructive">{errors.username}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="password">Kata Sandi</Label>
                                <Input id="password" type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} required />
                                {errors.password && <p className="text-sm text-destructive">{errors.password}</p>}
                            </div>
                            <div className="flex items-center space-x-2">
                                <input type="checkbox" id="remember" checked={data.remember} onChange={(e) => setData('remember', e.target.checked)} className="rounded border-gray-300" />
                                <Label htmlFor="remember" className="text-sm">Ingat saya</Label>
                            </div>
                            <Button type="submit" className="w-full" disabled={processing}>
                                {processing ? 'Masuk...' : 'Masuk'}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
