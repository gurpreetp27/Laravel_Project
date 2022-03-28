import request from '@/utils/request';

export function fetchSportList(query) {
  return request({
    url: '/get/sport/list',
    method: 'get',
    params: query,
  });
}

export function fetchTeamList(query) {
  return request({
    url: '/team',
    method: 'get',
    params: query,
  });
}

export function fetchSport(id) {
  return request({
    url: '/sport/' + id,
    method: 'get',
  });
}

export function fetchTeam(id) {
  return request({
    url: '/team/' + id,
    method: 'get',
  });
}
