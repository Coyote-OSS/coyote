export const JUNIOR = 'junior';
export const MID = 'mid';
export const SENIOR = 'senior';

export enum Model {
  Topic = 'Topic',
  User = 'User',
  Microblog = 'Microblog',
  Job = 'Job',
  Wiki = 'Wiki',
  Guide = 'Guide'
}

export interface Flag {
  id: number;
  name: string;
  text: string;
  created_at: Date;
  user: User;
  resources: FlagResource[];
  url: string;
}

export interface FlagType {
  id: number;
  name: string;
  description: string;
}

export interface FlagResource {
  resource_id: number;
  resource_type: string;
}

export interface User {
  id: number;
  name: string;
  deleted_at?: Date;
  is_blocked: boolean;
  is_blocked_perm: boolean;
  group_name?: string;
  photo: string;
  posts?: number;
  created_at?: Date;
  allow_sig?: boolean;
  allow_smilies?: boolean;
  allow_count?: boolean;
  location?: string;
  is_online?: boolean;
  is_incognito?: boolean;
  visited_at?: Date;
  sig?: string;
}

interface AssetMetadata {
  url: string;
  description: string;
  title: string;
}

export interface Asset {
  id: number;
  thumbnail: string;
  url: string;
  name?: string;
  mime?: string;
  filename?: string;
  metadata?: AssetMetadata;
}

export interface Microblog {
  id: number|null;
  parent_id?: number;
  votes: number;
  is_voted: boolean;
  is_subscribed: boolean;
  is_sponsored: boolean;
  is_editing?: boolean;
  is_read?: boolean;
  created_at: Date|null;
  updated_at: Date|null;
  deleted_at: Date|null;
  html: string;
  text: string;
  url: string;
  comments: Microblog[];
  user: User|null;
  assets: Asset[];
  permissions: MicroblogPermission;
  comments_count?: number;
  voters?: string[];
  metadata: string;
  tags: Tag[];
}

export interface MicroblogVoters {
  id: number;
  parent_id: number;
  users: string[];
}

export type PostVoters = Pick<MicroblogVoters, "id"|"users">

export interface Forum {
  id: number;
  slug: string;
  name: string;
  order: number;
  section: string;
  indent: number;
  tags?: Tag[];
  enable_tags: boolean;
}

export interface PollItem {
  id: number;
  text: string;
  total: number;
}

export interface Poll {
  title: string;
  length: number;
  max_items: number;
  expired_at?: Date;
  expired?: boolean;
  votes?: number[];
  items: PollItem[];
}

interface MicroblogPermission {
  update: boolean;
  moderate: boolean;
}

interface PostPermission {
  write: boolean;
  delete: boolean;
  update: boolean;
  accept: boolean;
}

interface PostModeratorPermission {
  delete: boolean;
  update: boolean;
  accept: boolean;
  merge: boolean;
  sticky: boolean;
  admAccess: boolean;
}

export interface PostComment {
  id: number;
  post_id: number;
  created_at: Date|null;
  updated_at: Date|null;
  text: string;
  html: string;
  user: User;
  editable?: boolean;
  is_read?: boolean;
  is_editing?: boolean;
  url: string;
  metadata: string;
}

export interface Post {
  id: number;
  user?: User;
  user_id: number|null;
  user_name?: string;
  editor?: User;
  deleter_name?: string;
  delete_reason?: string;
  created_at: Date;
  updated_at: Date|null;
  deleted_at: Date|null;
  text: string;
  html: string;
  score: number;
  orderingScore: number;
  url: string;
  is_read: boolean;
  is_locked: boolean;
  is_subscribed: boolean;
  is_accepted: boolean;
  is_voted: boolean;
  is_editing?: boolean;
  permissions: PostPermission;
  moderatorPermissions: PostModeratorPermission;
  comments: PostComment[];
  comments_count: number;
  assets: Asset[];
  edit_count?: number;
  metadata?: string;
  voters?: string[];
  parentPostId: number|null;
  childrenFolded: boolean;
  type: 'regular'|'obscured';
  highlighted: boolean;
}

export interface TreePost {
  post: Post;
  treeItem: TreePostItem;
}

export interface TreePostItem {
  indent: number;
  level: number;
  linksToParent: boolean;
  parentLevels: number[],
  linksToChildren: boolean;
  hasDeeperChildren: boolean;
  childrenAuthors: User[];
}

export interface SubTreeItem {
  post: Post;
  nestLevel: number;
  subtreeNestLevel: number;
  hasNextSibling: boolean;
  hasChildren: boolean;
}

export interface PostLog {
  id: number;
  post_id: number;
  text: string;
  ip: string;
  browser: string;
  created_at: Date;
  user: User;
  user_name?: string;
  title: string;
}

export interface Tag {
  id?: number;
  name: string;
  url?: string;
  real_name?: string;
  logo?: string;
  priority?: number;
  text?: string;
  topics?: number;
  microblogs?: number;
  jobs?: number;
}

export interface Topic {
  id: number;
  is_locked: boolean;
  is_sticky?: boolean;
  is_read?: boolean;
  title: string;
  first_post_id?: number;
  is_subscribed?: boolean;
  tags?: Tag[];
  subscribers?: number;
  owner_id?: number|null;
  discuss_mode: 'tree'|'linear';
  treeSelectedSubtree: boolean;
  treeSelectedSubtreePostId: number;
}

export interface Message {
  id: number;
  user: User;
  created_at: Date;
  read_at: Date;
  excerpt: string;
  text: string;
  text_id: number;
  sequential: boolean;
  folder: MessageFolder;
}

export interface Paginator {
  data: Microblog[]|Post[]|Guide[];
  current_page: number;
  from: number;
  last_page: number;
  path: string;
  per_page: number;
  to: number;
  total: number;
}

export interface JobLocation {
  label?: string;
  street?: string;
  street_number?: string;
  city?: string;
  country?: string;
}

export interface JobFeature {
  id: number;
  default: string;
  name: string;
  value: string;
  checked: boolean;
}

export enum Rate {
  monthly = 'miesiecznie',
  yearly = 'rocznie',
  weekly = 'tygodniowo',
  hourly = 'godzinowo'
}

export enum Employment {
  employment = 'Umowa o pracę',
  mandatory = 'Umowa zlecenie',
  contract = 'Umowa o dzieło',
  b2b = 'Kontrakt B2B'
}

export enum Seniority {
  student = 'Stażysta',
  junior = 'Junior',
  mid = 'Mid-level',
  senior = 'Senior',
  lead = 'Lead',
  manager = 'Manager'
}

export enum MessageFolder {
  inbox = 1,
  sentbox
}

export interface Currency {
  id: number;
  name: string;
  symbol: string;
}

export interface Job {
  id: number;
  plan_id: number;
  title: string;
  user?: User;
  locations: JobLocation[];
  enable_apply: boolean;
  email: string;
  tags: Tag[];
  features: JobFeature[];
  description: string;
  recruitment: string;
  salary_from: number;
  salary_to: number;
  currency_id: number;
  is_gross: boolean;
  is_remote: boolean;
  remote_range: number;
  rate: Rate;
  employment: Employment;
  seniority: Seniority;
  firm?: Firm;
  apply_type: string;
  application_url?: string;
  application_redirects?: number;
}

export interface Application {
  email: string;
  name: string;
  github: string;
  text: string;
  phone: string;
  remember: boolean;
  salary: string;
  dismissal_period: string;
  cv: string|null;
}

export interface Firm {
  id: number;
  name: string;
  is_agency: boolean;
  city: string;
  country: string;
  street: string;
  street_number: string;
  postcode: string;
  latitude: number;
  longitude: number;
  employees: number;
  founded: number;
  youtube_url: string;
  logo: string|null;
  assets: Asset[];
  benefits: string[];
  website: string;
  description: string;
}

interface GuidePermission {
  update: boolean;
}

export interface Comment {
  id: number;
  parent_id: number;
  text: string;
  html: string;
  assets: Asset[];
}

export interface Guide {
  id: number;
  title: string;
  slug: string;
  url: string;
  excerpt: string;
  excerpt_html: string;
  text: string;
  html: string;
  permissions: GuidePermission;
  is_editing: boolean;
  comments: Comment[];
  votes: number;
  subscribers: number;
  is_voted: boolean;
  is_subscribed: boolean;
  tags: Tag[];
  user: User;
  created_at: Date;
  comments_count: number;
  role: Seniority;
  assets: Asset[];
}

export interface Notification {
  id: string;
}

export interface Emojis {
  categories: Category[],
  subcategories: Subcategory[],
  emoticons: {[key: string]: Emoji}
}

interface Category {
  name: string,
  subcategories: string[]
}

interface Subcategory {
  name: string;
  emojis: string[];
}

export interface Emoji {
  id: string;
  name: string;
  keywords: string[];
  unified: string;
}
