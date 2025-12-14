import PublicLayout from '@/Layouts/PublicLayout';
import type { Campaign } from '@/types';
import { Link, router } from '@inertiajs/react';
import { useState } from 'react';

type Props = {
    campaign: Campaign;
    midtransToken: string;
    updates: Array<{ id: string; title: string; content: string; published_at: string }>;
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

                    {/* Card 1: Cover Image + Progress Bar */}
                    <div className="mb-8 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="flex flex-col lg:flex-row">
                            {/* Left Section: Cover Image */}
                            <div className="flex-shrink-0 lg:w-1/2">
                                <div className="relative">
                                    <img src={campaign.cover_image} alt={campaign.title} className="h-96 w-full rounded-xl object-cover" />
                                </div>
                            </div>

                            {/* Right Section: Campaign Details */}
                            <div className="lg:w-1/2 lg:pl-8">
                                <h1 className="text-3xl font-semibold text-gray-900">{campaign.title}</h1>

                                {/* Progress Bar */}
                                {campaign.goal_type === 'AMOUNT' && (
                                    <ProgressBar totalPaid={campaign.total_paid} targetAmount={campaign.target_amount} />
                                )}

                                {/* Donasi Button */}
                                {campaign.status === 'ACTIVE' && (
                                    <Link
                                        href={`/d/${campaign.slug}`}
                                        className="mt-6 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-3 text-lg font-semibold text-white transition hover:bg-emerald-700"
                                    >
                                        Donasi Sekarang
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                            <path
                                                d="M9 18l6-6-6-6"
                                                stroke="currentColor"
                                                strokeWidth="2"
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                            />
                                        </svg>
                                    </Link>
                                )}

                                {/* Status */}
                                <div className="mt-4 text-sm text-slate-600">
                                    Status: <span className="font-semibold text-slate-700">{campaign.status === 'ACTIVE' ? 'Aktif' : 'Ditutup'}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Card 2: Tabs (Deskripsi, Kabar Terbaru, Donatur) */}
                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="mb-6 flex space-x-6">
                            <button
                                onClick={() => setActiveTab('description')}
                                className={`text-lg font-semibold ${activeTab === 'description' ? 'text-emerald-600' : 'text-slate-500'}`}
                            >
                                Deskripsi
                            </button>
                            <button
                                onClick={() => setActiveTab('updates')}
                                className={`text-lg font-semibold ${activeTab === 'updates' ? 'text-emerald-600' : 'text-slate-500'}`}
                            >
                                Kabar Terbaru
                            </button>
                            <button
                                onClick={() => setActiveTab('donors')}
                                className={`text-lg font-semibold ${activeTab === 'donors' ? 'text-emerald-600' : 'text-slate-500'}`}
                            >
                                Donatur
                            </button>
                        </div>

                        {/* Display content based on active tab */}
                        {activeTab === 'description' && (
                            <div>
                                <h2 className="text-xl font-semibold text-slate-800">Deskripsi</h2>
                                <div className="mt-4 text-slate-600">
                                    <span dangerouslySetInnerHTML={{ __html: campaign.description }} />
                                </div>
                            </div>
                        )}

                        {activeTab === 'updates' && (
                            <div>
                                <h2 className="text-xl font-semibold text-slate-800">Kabar Terbaru</h2>
                                <div className="mt-4">
                                    {updates && updates.length > 0 ? (
                                        updates.map((update) => (
                                            <div key={update.id} className="mb-6 rounded-lg border border-slate-200 p-4">
                                                <h3 className="text-lg font-semibold text-slate-800">{update.title}</h3>
                                                <p className="mt-2 text-slate-600" dangerouslySetInnerHTML={{ __html: update.content }} />
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
                                <div className="mt-4">
                                    {campaign.donations && campaign.donations.length > 0 ? (
                                        campaign.donations.map((donor) => (
                                            <div key={donor.id} className="mb-2 text-slate-600">
                                                <span className="font-semibold">{donor.name}</span> -{' '}
                                                <span className="text-slate-500">Rp {formatIDR(donor.amount)}</span>
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
