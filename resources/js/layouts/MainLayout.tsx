import { PropsWithChildren, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Toaster } from '@/components/ui/sonner';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet';
import { ThemeToggle } from '@/components/ThemeToggle';
import {
    LayoutDashboard,
    Users,
    Wifi,
    Server,
    Network,
    Settings,
    LogOut,
    User,
    CreditCard,
    Menu,
    Receipt,
    Ticket,
    FileText,
    Database,
    Activity,
    BarChart3,
    MessageSquare,
} from 'lucide-react';

const adminNav = [
    { label: 'Dasbor', href: 'admin.dashboard', icon: LayoutDashboard },
    { label: 'Pelanggan', href: 'admin.customers.index', icon: Users },
    { label: 'Paket', href: 'admin.plans.index', icon: Wifi },
    { label: 'Router', href: 'admin.routers.index', icon: Server },
    { label: 'Bandwidth', href: 'admin.bandwidth.index', icon: Network },
    { label: 'Pool IP', href: 'admin.pools.index', icon: Network },
    { label: 'Isi Ulang', href: 'admin.recharges.index', icon: Receipt },
    { label: 'Voucher', href: 'admin.vouchers.index', icon: Ticket },
    { label: 'Transaksi', href: 'admin.transactions.index', icon: FileText },
    { label: 'Gateway Pembayaran', href: 'admin.payment-gateways.index', icon: CreditCard },
    { label: 'Siklus Billing', href: 'admin.billing-cycles.index', icon: Calendar },
    { label: 'Kirim Pesan', href: 'admin.messages.send', icon: MessageSquare },
    { label: 'Laporan', href: 'admin.reports.index', icon: BarChart3 },
    { label: 'Log Aktivitas', href: 'admin.logs.index', icon: Activity },
    { label: 'Log Pesan', href: 'admin.message-logs.index', icon: MessageSquare },
    { label: 'Field Kustom', href: 'admin.custom-fields.index', icon: Settings },
    { label: 'Pengaturan', href: 'admin.settings.index', icon: Settings },
];

const customerNav = [
    { label: 'Dasbor', href: 'customer.dashboard', icon: LayoutDashboard },
    { label: 'Paket', href: 'customer.plans.index', icon: Wifi },
    { label: 'Transaksi', href: 'customer.transactions.index', icon: FileText },
    { label: 'Profil', href: 'customer.profile', icon: User },
];

function SidebarContent({ onNavigate }: { onNavigate?: () => void }) {
    const { url, auth } = usePage().props as any;
    const guard = auth?.guard;
    const user = auth?.user;
    const navItems = guard === 'admin' ? adminNav : customerNav;

    const handleLogout = () => {
        const logoutUrl = guard === 'customer' ? route('customer.logout') : route('admin.logout');
        window.axios.post(logoutUrl).then(() => {
            window.location.href = guard === 'customer'
                ? route('customer.login')
                : route('admin.login');
        });
    };

    return (
        <div className="flex h-full flex-col bg-background">
            <div className="flex items-center gap-2 px-4 py-4 border-b">
                <Link
                    href={guard === 'customer' ? route('customer.dashboard') : route('admin.dashboard')}
                    onClick={onNavigate}
                    className="flex items-center gap-2"
                >
                    <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground text-sm font-bold">
                        B
                    </div>
                    <span className="text-lg font-semibold">Badar Net</span>
                </Link>
            </div>

            <nav className="flex-1 space-y-1 px-2 py-4">
                {navItems.map((item) => {
                    const href = route(item.href);
                    const isActive = url === new URL(href).pathname || url === href;
                    return (
                        <Link
                            key={item.href}
                            href={href}
                            onClick={onNavigate}
                            className={`flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors ${
                                isActive
                                    ? 'bg-primary text-primary-foreground'
                                    : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                            }`}
                        >
                            <item.icon className="h-4 w-4" />
                            {item.label}
                        </Link>
                    );
                })}
            </nav>

            <div className="border-t p-4">
                {user && (
                    <div className="space-y-3">
                        <div className="flex items-center gap-3">
                            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-muted text-sm font-medium">
                                {(user.fullname || user.username || '').charAt(0).toUpperCase()}
                            </div>
                            <div className="flex-1 min-w-0">
                                <p className="text-sm font-medium truncate">{user.fullname || user.username}</p>
                                <p className="text-xs text-muted-foreground truncate">{user.email || ''}</p>
                            </div>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            className="w-full justify-start text-destructive hover:text-destructive"
                            onClick={handleLogout}
                        >
                            <LogOut className="mr-2 h-4 w-4" />
                            Keluar
                        </Button>
                    </div>
                )}
            </div>
        </div>
    );
}

export default function MainLayout({ children }: PropsWithChildren) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const { auth } = usePage().props as any;
    const guard = auth?.guard;

    return (
        <div className="min-h-screen bg-background">
            {/* Desktop Sidebar */}
            <aside className="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-64 lg:flex-col border-r">
                <SidebarContent />
            </aside>

            {/* Mobile Sidebar */}
            <Sheet open={sidebarOpen} onOpenChange={setSidebarOpen}>
                <SheetContent side="left" className="w-64 p-0">
                    <SidebarContent onNavigate={() => setSidebarOpen(false)} />
                </SheetContent>
            </Sheet>

            {/* Main Content */}
            <div className="lg:pl-64">
                {/* Top Bar */}
                <header className="sticky top-0 z-40 flex h-14 items-center gap-4 border-b bg-background px-4 sm:px-6 lg:px-8">
                    <Sheet>
                        <SheetTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon"
                                className="lg:hidden"
                                onClick={() => setSidebarOpen(true)}
                            >
                                <Menu className="h-5 w-5" />
                                <span className="sr-only">Buka menu</span>
                            </Button>
                        </SheetTrigger>
                    </Sheet>
                    <div className="flex-1" />
                    <ThemeToggle />
                    <div className="text-sm text-muted-foreground capitalize">
                        {guard === 'admin' ? 'Panel Admin' : 'Panel Pelanggan'}
                    </div>
                </header>

                {/* Page Content */}
                <main className="p-4 sm:p-6 lg:p-8">
                    {children}
                </main>
            </div>

            <Toaster />
        </div>
    );
}
