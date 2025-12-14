import React, { useState } from "react";
import { Link, router } from "@inertiajs/react";
import PublicLayout from "@/Layouts/PublicLayout";
import type { Campaign, CampaignType } from "@/types";

type Props = {
  type: CampaignType;
  campaigns: Campaign[];
};

function formatIDR(value: number) {
  return new Intl.NumberFormat("id-ID").format(Number(value || 0));
}

function percent(total: number, target: number) {
  const t = Number(target || 0);
  const x = Number(total || 0);
  if (t <= 0) return 0;
  return Math.min(100, Math.round((x / t) * 100));
}

function Badge({ type }: { type: CampaignType }) {
  const isCrowd = type === "CROWDFUND";
  const cls = isCrowd
    ? "bg-emerald-100 text-emerald-700 border-emerald-200"
    : "bg-sky-100 text-sky-700 border-sky-200";

  return (
    <span
      className={`inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-semibold ${cls}`}
    >
      <span
        className={`h-1.5 w-1.5 rounded-full ${
          isCrowd ? "bg-emerald-600" : "bg-sky-600"
        }`}
      />
      {isCrowd ? "Galang Dana" : "Donasi"}
    </span>
  );
}

function ProgressBar({
  totalPaid,
  targetAmount,
}: {
  totalPaid: number;
  targetAmount: number;
}) {
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
        <div
          className="h-full rounded-full bg-emerald-600 transition-all"
          style={{ width: `${p}%` }}
        />
      </div>
    </div>
  );
}

function CampaignCard({ c }: { c: Campaign }) {
  const hasTarget = c.goal_type === "AMOUNT" && Number(c.target_amount) > 0;

  return (
    <div className="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
      <div className="relative aspect-[16/9] w-full overflow-hidden bg-slate-100">
        {c.cover_image ? (
          <img
            src={c.cover_image}
            alt={c.title}
            className="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]"
            loading="lazy"
          />
        ) : (
          <div className="flex h-full w-full items-center justify-center text-slate-400">
            <span className="text-sm">No image</span>
          </div>
        )}
        <div className="absolute left-3 top-3">
          <Badge type={c.type} />
        </div>
      </div>

      <div className="p-5">
        <h3 className="line-clamp-2 text-base font-bold text-slate-900">
          {c.title}
        </h3>

        <p className="mt-2 line-clamp-2 text-sm text-slate-600">
          {/* Menampilkan deskripsi dengan HTML yang sudah diformat */}
          <span dangerouslySetInnerHTML={{ __html: c.description }} />
        </p>

        {hasTarget ? (
          <ProgressBar totalPaid={c.total_paid} targetAmount={c.target_amount} />
        ) : (
          <div className="mt-3 text-xs text-slate-500">
            Terkumpul:{" "}
            <span className="font-semibold text-slate-700">
              Rp {formatIDR(c.total_paid)}
            </span>
          </div>
        )}

        <div className="mt-4 flex items-center justify-between">
          <Link
            href={`/${c.slug}`}
            className="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
          >
            Lihat Detail
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

          <span className="text-xs text-slate-500">
            Status:{" "}
            <span className="font-semibold text-slate-700">
              {c.status === "ACTIVE" ? "Aktif" : c.status}
            </span>
          </span>
        </div>
      </div>
    </div>
  );
}

export default function Index({ type, campaigns }: Props) {
  const [activeType, setActiveType] = useState<CampaignType>(
    type === "CROWDFUND" ? "CROWDFUND" : "DONATION"
  );

  const switchType = (nextType: CampaignType) => {
    setActiveType(nextType); // Update state tanpa merubah URL
  };

  return (
    <PublicLayout>
      <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">
            Program Donasi
          </h1>
          <p className="mt-1 text-sm text-slate-600">
            Pilih program yang ingin kamu dukung.
          </p>
        </div>

        <div className="flex items-center gap-2">
          <button
            type="button"
            onClick={() => switchType("DONATION")}
            className={`rounded-full px-4 py-2 text-sm font-semibold transition ${
              activeType === "DONATION"
                ? "bg-sky-600 text-white"
                : "bg-white text-slate-700 border border-slate-200 hover:bg-slate-100"
            }`}
          >
            Donasi
          </button>

          <button
            type="button"
            onClick={() => switchType("CROWDFUND")}
            className={`rounded-full px-4 py-2 text-sm font-semibold transition ${
              activeType === "CROWDFUND"
                ? "bg-emerald-600 text-white"
                : "bg-white text-slate-700 border border-slate-200 hover:bg-slate-100"
            }`}
          >
            Galang Dana
          </button>
        </div>
      </div>

      <section className="mt-8">
        {campaigns.length === 0 ? (
          <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <p className="text-sm text-slate-600">
              Belum ada campaign yang tersedia.
            </p>
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {campaigns.map((c) => (
              <CampaignCard key={c.id} c={c} />
            ))}
          </div>
        )}
      </section>
    </PublicLayout>
  );
}
