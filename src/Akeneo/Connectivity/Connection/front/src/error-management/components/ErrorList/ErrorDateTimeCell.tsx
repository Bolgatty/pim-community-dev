import React, {FC} from 'react';
import TableCell from '../../../common/components/Table/TableCell';
import {ClockIcon, DateIcon} from '../../../common/icons';
import styled from '../../../common/styled-with-theme';
import {useDateFormatter} from '../../../shared/formatter/use-date-formatter';

type Props = {
    timestamp: number;
};

const useFormatTimestampToDate = () => {
    const formatDateTime = useDateFormatter();
    return (timestamp: number) =>
        formatDateTime(timestamp, {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        });
};

const useFormatTimestampToTime = () => {
    const formatDateTime = useDateFormatter();
    return (timestamp: number) =>
        formatDateTime(timestamp, {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
};

const ErrorDateTimeCell: FC<Props> = ({timestamp}) => {
    const formatTimestampToDate = useFormatTimestampToDate();
    const formatTimestampToTime = useFormatTimestampToTime();

    return (
        <Container collapsing>
            <DateTimeRow>
                <Calendar />
                <DateTimeText>{formatTimestampToDate(timestamp)}</DateTimeText>
            </DateTimeRow>
            <DateTimeRow>
                <Clock />
                <DateTimeText>{formatTimestampToTime(timestamp)}</DateTimeText>
            </DateTimeRow>
        </Container>
    );
};

const Container = styled(TableCell)`
    color: ${({theme}) => theme.color.grey140};
`;

const DateTimeRow = styled.div`
    line-height: ${({theme}) => theme.fontSize.default};
    color: ${({theme}) => theme.color.grey140};
    padding: 5px 0px;
`;

const DateTimeText = styled.span`
    margin-left: 4px;
    vertical-align: text-bottom;
`;

const Calendar = styled(DateIcon)`
    width: 24px;
    height: 24px;
    vertical-align: middle;
`;

const Clock = styled(ClockIcon)`
    width: 24px;
    height: 24px;
    vertical-align: middle;
`;

export {ErrorDateTimeCell};
