export type CampaignType = "DONATION" | "CROWDFUND";
export type GoalType = "AMOUNT" | "NONE";
export type CampaignStatus = "DRAFT" | "ACTIVE" | "CLOSED" | "ARCHIVED";

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
