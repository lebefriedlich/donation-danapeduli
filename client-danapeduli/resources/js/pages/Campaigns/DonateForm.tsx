import PublicLayout from '@/Layouts/PublicLayout';
import { useForm } from '@inertiajs/react';
import React from 'react';
import axios from 'axios';

type Props = {
    campaign: {
        id: number;
        title: string;
        cover_image: string;
    };
    midtransToken: string; // Token yang diterima dari backend
};

export default function DonateForm({ campaign, midtransToken }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        is_anonymous: false,
        message: '',
        amount: 0,
    });

    // Menghandle submit form
    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        try {
            const response = await axios.post(`/donate/${campaign.id}`, data);

            handlePayment(response.data.snap_token);
        } catch (error) {
            alert('Terjadi kesalahan saat memproses donasi');
            console.error(error);
        }
    };

    // Fungsi untuk memproses pembayaran menggunakan Midtrans
    const handlePayment = (snap_token: string) => {
        // Inisialisasi Midtrans Snap dengan token yang diterima dari backend
        window.snap.pay(snap_token, {
            onSuccess: function (result) {
                console.log(result);
                alert('Pembayaran sukses!');
            },
            onPending: function (result) {
                console.log(result);
                alert('Pembayaran pending.');
            },
            onError: function (result) {
                console.log(result);
                alert('Pembayaran gagal.');
            },
            onClose: function () {
                alert('Pembayaran dibatalkan.');
            },
        });
    };

    return (
        <PublicLayout>
            <div className="bg-white">
                <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
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
                    <h1 className="mb-8 text-3xl font-semibold text-gray-900">Isi Data Donasi</h1>

                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="mb-6 text-2xl font-semibold text-slate-800">Donasi untuk {campaign.title}</h2>

                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* Nama */}
                            <div>
                                <label htmlFor="name" className="block text-sm font-medium text-slate-700">
                                    Nama Lengkap
                                </label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    className="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-3 text-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-base"
                                    required
                                />
                                {errors.name && <span className="text-sm text-red-500">{errors.name}</span>}
                            </div>

                            {/* Apakah Disamarkan */}
                            <div className="flex items-center justify-between">
                                <label htmlFor="is_anonymous" className="text-sm font-medium text-slate-700">
                                    Sembunyikan nama saya (Orang Baik)
                                </label>
                                <button
                                    type="button"
                                    onClick={() => setData('is_anonymous', !data.is_anonymous)}
                                    className={`relative inline-flex h-8 w-14 items-center rounded-full transition ${
                                        data.is_anonymous ? 'bg-emerald-600' : 'bg-gray-300'
                                    }`}
                                >
                                    <span
                                        className={`inline-block h-6 w-6 transform rounded-full bg-white transition ${
                                            data.is_anonymous ? 'translate-x-7' : 'translate-x-1'
                                        }`}
                                    />
                                </button>
                            </div>

                            {/* Email */}
                            <div>
                                <label htmlFor="email" className="block text-sm font-medium text-slate-700">
                                    Email
                                </label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-3 text-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-base"
                                    required
                                />
                                {errors.email && <span className="text-sm text-red-500">{errors.email}</span>}
                            </div>

                            {/* Nominal Uang yang Akan Didonasikan */}
                            <div>
                                <label htmlFor="amount" className="block text-sm font-medium text-slate-700">
                                    Nominal Donasi (Rp)
                                </label>
                                <input
                                    id="amount"
                                    name="amount"
                                    type="text"
                                    inputMode="numeric"
                                    value={data.amount > 0 ? data.amount.toLocaleString('id-ID') : ''}
                                    onChange={(e) => setData('amount', parseInt(e.target.value.replace(/\./g, ''), 10) || 0)}
                                    className="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-3 text-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-lg"
                                    required
                                />
                                {errors.amount && <span className="text-sm text-red-500">{errors.amount}</span>}
                            </div>

                            {/* Pesan (Opsional) */}
                            <div>
                                <label htmlFor="message" className="block text-sm font-medium text-slate-700">
                                    Pesan (Opsional)
                                </label>
                                <textarea
                                    id="message"
                                    name="message"
                                    value={data.message}
                                    onChange={(e) => setData('message', e.target.value)}
                                    className="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-3 text-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-base"
                                    rows={4}
                                />
                                {errors.message && <span className="text-sm text-red-500">{errors.message}</span>}
                            </div>

                            {/* Submit Button */}
                            <div className="mt-4 flex justify-end">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-3 text-lg font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                                >
                                    {processing ? 'Memproses...' : 'Kirim Donasi'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}
