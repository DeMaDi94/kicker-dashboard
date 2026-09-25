/** NEWS-02, NEWS-03 — mirrors ListNewsService's NewsLine. */
export type NewsLine = {
    id: number;
    text: string;
    authorName: string;
    postedAt: string;
    edited: boolean;
    mayChange: boolean;
};
