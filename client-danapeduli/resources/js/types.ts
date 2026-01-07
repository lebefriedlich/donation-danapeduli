export type CampaignType = "DONATION" | "CROWDFUND";
export type CampaignFilterType = "ALL" | CampaignType;
export type GoalType = "AMOUNT" | "NONE";
export type CampaignStatus = "DRAFT" | "ACTIVE" | "CLOSED" | "ARCHIVED";

export type Donation = {
  id: number;
  name: string;
  amount: number;
  is_anonymous?: boolean;
  message?: string | null;
};

export type Campaign = {
  id: number;
  slug: string;
  title: string;
  description: string;

  type: CampaignType;
  goal_type: GoalType;

  target_amount: number;
  total_paid: number;

  status: CampaignStatus;

  cover_image?: string | null; // idealnya URL publik

  donations?: Donation[];
};

export type CampaignUpdate = {
  id: number;
  title: string;
  content: string;
  attachment?: string | null;
  published_at?: string | null;

  is_financial_update?: boolean;
  disbursed_amount?: number | null;
};
