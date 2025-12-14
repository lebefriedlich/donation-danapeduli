import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

type Props = PropsWithChildren<{
    title?: string;
}>;

export default function PublicLayout({ children, title }: Props) {
    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/80 backdrop-blur">
                <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                    <Link href="/" className="flex items-center gap-2">
                        <img src="/favicon.png" alt="Dana Peduli" className="h-9 w-9" />
                        <div className="leading-tight">
                            <div className="text-sm font-black">Dana Peduli</div>
                            <div className="text-[11px] text-slate-500">Wadah Kepedulian untuk Sesama</div>
                        </div>
                    </Link>
                </div>
            </header>

            <main className="mx-auto max-w-6xl px-4 py-8">
                {title ? (
                    <div className="mb-6">
                        <h1 className="text-2xl font-black tracking-tight">{title}</h1>
                    </div>
                ) : null}

                {children}
            </main>

            <footer className="border-t border-slate-500 bg-white">
                <div className="mx-auto max-w-6xl px-4 py-5 text-center text-xs text-slate-500">© {new Date().getFullYear()} Dana Peduli</div>
            </footer>
        </div>
    );
}
