import { DateFormatter, parseDateTime } from '@internationalized/date';
import { toDate } from 'reka-ui/date';

export function useFormatters() {
    const formatCurrency = (value: number) =>
        new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
            currencySign: 'accounting',
            minimumFractionDigits: 2,
        }).format(value);

    const formatNumber = (value: number) => new Intl.NumberFormat('en-PH').format(value);

    const dateFormatter = new DateFormatter('en-US', {
        weekday: 'long', // Monday
        month: 'long', // September
        day: 'numeric', // 22
        year: 'numeric', // 2025
        timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    });

    // formatter for time only
    const timeFormatter = new DateFormatter('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
        timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    });

    function toJsDate(date: string, time: string) {
        return toDate(parseDateTime(`${date}T${time}`));
    }

    function getFullName(lastName: string, firstName: string, middleName: string | null) {
        return `${lastName}, ${firstName}` + `${middleName ? ` ${middleName[0]}.` : ''}`;
    }

    return { formatCurrency, formatNumber, dateFormatter, timeFormatter, toJsDate, getFullName };
}
