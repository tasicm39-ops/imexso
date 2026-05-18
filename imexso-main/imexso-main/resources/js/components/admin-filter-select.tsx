import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';

const ALL_VALUE = '__all__';

export type AdminFilterSelectOption = {
    value: string;
    label: string;
};

type AdminFilterSelectProps = {
    value: string;
    onValueChange: (value: string) => void;
    options: AdminFilterSelectOption[];
    placeholder: string;
    allowAll?: boolean;
    className?: string;
    triggerClassName?: string;
};

export function AdminFilterSelect({
    value,
    onValueChange,
    options,
    placeholder,
    allowAll = true,
    className,
    triggerClassName,
}: AdminFilterSelectProps) {
    const selectValue = allowAll ? (value === '' ? ALL_VALUE : value) : value;

    return (
        <Select
            value={selectValue}
            onValueChange={(next) => onValueChange(allowAll && next === ALL_VALUE ? '' : next)}
        >
            <SelectTrigger
                className={cn(
                    'w-[180px] border-input bg-background text-foreground shadow-xs',
                    triggerClassName,
                )}
            >
                <SelectValue placeholder={placeholder} />
            </SelectTrigger>
            <SelectContent
                className={cn(
                    'border-border bg-black text-white',
                    className,
                )}
                position="popper"
            >
                {allowAll && (
                    <SelectItem
                        value={ALL_VALUE}
                        className="focus:bg-neutral-800 focus:text-white"
                    >
                        {placeholder}
                    </SelectItem>
                )}
                {options.map((option) => (
                    <SelectItem
                        key={option.value}
                        value={option.value}
                        className="focus:bg-neutral-800 focus:text-white"
                    >
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}
