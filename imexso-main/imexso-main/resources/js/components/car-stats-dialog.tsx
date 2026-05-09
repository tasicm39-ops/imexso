import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import type { ReactNode } from 'react';

type DayStat = { date: string; count: number };

type CarStatsDialogProps = {
    title: string;
    description: string;
    data: DayStat[];
    color: string;
    children: ReactNode;
};

const BAR_WIDTH = 40;
const BAR_GAP = 8;
const MAX_HEIGHT = 120;

function formatDate(dateStr: string): string {
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
}

export default function CarStatsDialog({ title, description, data, color, children }: CarStatsDialogProps) {
    const max = Math.max(...data.map((d) => d.count), 1);
    const total = data.reduce((sum, d) => sum + d.count, 0);
    const avg = data.length > 0 ? (total / data.length).toFixed(1) : '0';
    const svgWidth = data.length * BAR_WIDTH + (data.length - 1) * BAR_GAP;

    return (
        <Dialog>
            <DialogTrigger asChild>{children}</DialogTrigger>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    <DialogDescription>{description}</DialogDescription>
                </DialogHeader>

                <div className="flex gap-6 border-b pb-4">
                    <div>
                        <div className="text-2xl font-bold">{total}</div>
                        <div className="text-xs text-muted-foreground">Total</div>
                    </div>
                    <div>
                        <div className="text-2xl font-bold">{avg}</div>
                        <div className="text-xs text-muted-foreground">Daily avg</div>
                    </div>
                </div>

                <div className="flex justify-center py-4">
                    <svg width={svgWidth} height={MAX_HEIGHT + 24}>
                        {data.map((d, i) => {
                            const h = (d.count / max) * MAX_HEIGHT || 2;
                            const x = i * (BAR_WIDTH + BAR_GAP);
                            return (
                                <g key={d.date}>
                                    <rect
                                        x={x}
                                        y={MAX_HEIGHT - h}
                                        width={BAR_WIDTH}
                                        height={h}
                                        rx={3}
                                        fill={d.count > 0 ? color : '#e5e7eb'}
                                    />
                                    <text
                                        x={x + BAR_WIDTH / 2}
                                        y={MAX_HEIGHT - h - 4}
                                        textAnchor="middle"
                                        className="fill-foreground text-[11px] font-medium"
                                    >
                                        {d.count}
                                    </text>
                                    <text
                                        x={x + BAR_WIDTH / 2}
                                        y={MAX_HEIGHT + 16}
                                        textAnchor="middle"
                                        className="fill-muted-foreground text-[10px]"
                                    >
                                        {formatDate(d.date)}
                                    </text>
                                </g>
                            );
                        })}
                    </svg>
                </div>

                <div className="space-y-1">
                    {data.map((d) => (
                        <div key={d.date} className="flex items-center justify-between text-sm">
                            <span className="text-muted-foreground">{formatDate(d.date)}</span>
                            <span className="font-medium tabular-nums">{d.count}</span>
                        </div>
                    ))}
                </div>
            </DialogContent>
        </Dialog>
    );
}
