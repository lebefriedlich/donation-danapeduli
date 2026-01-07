import PublicLayout from '@/Layouts/PublicLayout';
import type { Campaign } from '@/types';
import { Link } from '@inertiajs/react';
import { useState } from 'react';

type Props = {
    campaign: Campaign;
    midtransToken: string;
    updates: Array<{
        id: string;
        title: string;
        content: string;
        published_at?: string | null;
        is_financial_update?: boolean;
        disbursed_amount?: number;
        attachment?: string;
    }>;
};

function formatIDR(value: number) {
    return new Intl.NumberFormat('id-ID').format(Number(value || 0));
}

function percent(total: number, target: number) {
    const t = Number(target || 0);
    const x = Number(total || 0);
    if (t <= 0) return 0;
    return Math.min(100, Math.round((x / t) * 100));
}

function ProgressBar({ totalPaid, targetAmount }: { totalPaid: number; targetAmount: number }) {
    const p = percent(totalPaid, targetAmount);

    return (
        <div className="mt-3">
            <div className="flex items-center justify-between text-xs text-slate-600">
                <span>{p}%</span>
                <span>
                    Rp {formatIDR(totalPaid)} / Rp {formatIDR(targetAmount)}
                </span>
            </div>
            <div className="mt-1 h-2 w-full overflow-hidden rounded-full bg-slate-200">
                <div className="h-full rounded-full bg-emerald-600 transition-all" style={{ width: `${p}%` }} />
            </div>
        </div>
    );
}

export default function Show({ campaign, midtransToken, updates }: Props) {
    const [activeTab, setActiveTab] = useState<'updates' | 'donors' | 'description'>('description');

    return (
        <PublicLayout>
            <div className="bg-white">
                <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                    {/* Back Button */}
                    <div className="mb-6">
                        <button
                            onClick={() => window.history.back()}
                            className="inline-flex items-center gap-2 rounded-lg bg-gray-300 px-6 py-3 text-lg font-semibold text-gray-700 transition hover:bg-gray-400"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M15 19l-7-7 7-7" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" />
                            </svg>
                            Kembali
                        </button>
                    </div>

                    {/* Cover Image (full width) */}
                    <div className="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <img src={campaign.cover_image || ''} alt={campaign.title} className="h-full w-full object-cover" />
                    </div>

                    {/* Campaign Details */}
                    <div className="mb-8 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 className="text-3xl font-semibold text-gray-900">{campaign.title}</h1>

                        {/* Progress Bar */}
                        {campaign.goal_type === 'AMOUNT' && (
                            <div className="mt-4">
                                <ProgressBar totalPaid={campaign.total_paid} targetAmount={campaign.target_amount} />
                            </div>
                        )}

                        {/* Donasi Button */}
                        {campaign.status === 'ACTIVE' && (
                            <Link
                                href={`/d/${campaign.slug}`}
                                className="mt-6 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-3 text-lg font-semibold text-white transition hover:bg-emerald-700"
                            >
                                Donasi Sekarang
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                    <path d="M9 18l6-6-6-6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                                </svg>
                            </Link>
                        )}

                        {/* Status */}
                        <div className="mt-4 text-sm text-slate-600">
                            Status: <span className="font-semibold text-slate-700">{campaign.status === 'ACTIVE' ? 'Aktif' : 'Ditutup'}</span>
                        </div>
                    </div>

                    {/* Card 2: Tabs (Deskripsi, Kabar Terbaru, Donatur) */}
                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="mb-6 border-b border-slate-200">
                            <div className="flex gap-6">
                                {[
                                    { key: 'description', label: 'Deskripsi' },
                                    { key: 'updates', label: 'Kabar Terbaru' },
                                    { key: 'donors', label: 'Donatur' },
                                ].map((tab) => (
                                    <button
                                        key={tab.key}
                                        onClick={() => setActiveTab(tab.key as 'description' | 'updates' | 'donors')}
                                        className={`relative pb-3 text-base font-semibold transition ${
                                            activeTab === tab.key
                                                ? 'text-emerald-600 after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-full after:bg-emerald-600'
                                                : 'text-slate-500 hover:text-slate-700'
                                        }`}
                                    >
                                        {tab.label}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Display content based on active tab */}
                        {activeTab === 'description' && (
                            <div>
                                <h2 className="text-xl font-semibold text-slate-800">Deskripsi Campaign</h2>

                                <div
                                    className="mt-4 leading-relaxed text-slate-700 [&>li]:mb-2 [&>ol]:mb-4 [&>ol]:list-decimal [&>ol]:pl-6 [&>p]:mb-4 [&>ul]:mb-4 [&>ul]:list-disc [&>ul]:pl-6 [&_strong]:font-semibold [&_strong]:text-slate-900"
                                    dangerouslySetInnerHTML={{ __html: campaign.description }}
                                />
                            </div>
                        )}

                        {activeTab === 'updates' && (
                            <div>
                                <h2 className="text-xl font-semibold text-slate-800">Kabar Terbaru</h2>

                                <div className="mt-4 space-y-6">
                                    {updates && updates.length > 0 ? (
                                        updates.map((update) => (
                                            <div key={update.id} className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                                {/* Header */}
                                                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                                    <div>
                                                        <h3 className="text-lg font-semibold text-slate-800">{update.title}</h3>

                                                        {update.published_at && (
                                                            <p className="mt-1 text-sm text-slate-500">
                                                                {new Date(update.published_at).toLocaleDateString('id-ID', {
                                                                    day: 'numeric',
                                                                    month: 'long',
                                                                    year: 'numeric',
                                                                })}
                                                            </p>
                                                        )}
                                                    </div>

                                                    {/* Badge */}
                                                    {update.is_financial_update ? (
                                                        <span className="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                            Laporan Dana
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                                            Update
                                                        </span>
                                                    )}
                                                </div>

                                                {/* Content */}
                                                <div
                                                    className="mt-4 leading-relaxed text-slate-700 [&>li]:mb-2 [&>ol]:mb-4 [&>ol]:list-decimal [&>ol]:pl-6 [&>p]:mb-4 [&>ul]:mb-4 [&>ul]:list-disc [&>ul]:pl-6 [&_strong]:font-semibold [&_strong]:text-slate-900"
                                                    dangerouslySetInnerHTML={{ __html: update.content }}
                                                />

                                                {/* Financial Info */}
                                                {update.is_financial_update && update.disbursed_amount && (
                                                    <div className="mt-4 rounded-lg bg-emerald-50 p-4">
                                                        <p className="text-sm text-emerald-700">Dana tersalurkan</p>
                                                        <p className="text-lg font-bold text-emerald-800">Rp {formatIDR(update.disbursed_amount)}</p>
                                                    </div>
                                                )}

                                                {/* Attachment */}
                                                {update.attachment && (
                                                    <div className="mt-4">
                                                        <a
                                                            href={update.attachment}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="inline-flex items-center gap-2 text-sm font-medium text-emerald-600 hover:underline"
                                                        >
                                                            📎 Lihat Lampiran
                                                        </a>
                                                    </div>
                                                )}
                                            </div>
                                        ))
                                    ) : (
                                        <p className="text-sm text-slate-500">Belum ada kabar terbaru.</p>
                                    )}
                                </div>
                            </div>
                        )}

                        {activeTab === 'donors' && (
                            <div>
                                <h2 className="text-xl font-semibold text-slate-800">Donatur</h2>

                                <div className="mt-4 space-y-3">
                                    {campaign.donations && campaign.donations.length > 0 ? (
                                        campaign.donations.map((donor) => (
                                            <div key={donor.id} className="rounded-lg border border-slate-200 p-3">
                                                <div className="flex items-center justify-between gap-3">
                                                    <span className="font-medium text-slate-700">
                                                        {donor.is_anonymous ? 'Orang Baik' : donor.name}
                                                    </span>

                                                    <span className="text-sm font-semibold text-emerald-600">Rp {formatIDR(donor.amount)}</span>
                                                </div>

                                                {donor.message && <p className="mt-2 text-sm text-slate-600">{donor.message}</p>}
                                            </div>
                                        ))
                                    ) : (
                                        <p className="text-sm text-slate-500">Belum ada donatur.</p>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}
